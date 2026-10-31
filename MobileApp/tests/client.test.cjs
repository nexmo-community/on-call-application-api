// Run with: node --experimental-vm-modules tests/client.test.cjs
// Exercise the real ES modules without Expo/native storage or network access.
const assert = require('assert').strict;
const fs = require('fs');
const path = require('path');
const vm = require('vm');

async function loadApi(availability = () => Promise.resolve(true), readToken = () => Promise.resolve(null)) {
  const context = vm.createContext({});
  const reads = [];
  const configurations = [];
  const requests = [];
  const response = { data: { token: 'login-token' } };
  const client = {
    post(url, body) {
      requests.push({ url, body });
      return Promise.resolve(response);
    },
  };
  const config = JSON.parse(fs.readFileSync(path.join(__dirname, '../config.json'), 'utf8'));
  function dependency(exports) {
    return new vm.SyntheticModule(Object.keys(exports), function () {
      for (const name of Object.keys(exports)) this.setExport(name, exports[name]);
    }, { context });
  }
  const dependencies = {
    axios: dependency({ create(options) { configurations.push(options); return client; } }),
    'expo-secure-store': dependency({
      isAvailableAsync() {
        const result = availability();
        // Keep a broken implementation's ignored rejection from ending the runner.
        // Return the original promise so its rejection is still observable by getClient.
        result.catch(() => {});
        return result;
      },
      getItemAsync(key) { reads.push(key); return readToken(); },
    }),
    '../config.json': dependency({ default: config }),
  };
  function sourceModule(name) {
    const filename = path.join(__dirname, '../api', name);
    return new vm.SourceTextModule(fs.readFileSync(filename, 'utf8'), { context, identifier: filename });
  }
  const clientModule = sourceModule('client.js');
  await clientModule.link(name => dependencies[name]);
  await clientModule.evaluate();
  const authenticationModule = sourceModule('authentication.js');
  await authenticationModule.link(name => {
    assert.equal(name, './client.js');
    return clientModule;
  });
  await authenticationModule.evaluate();
  return {
    getClient: clientModule.namespace.getClient,
    authenticateUser: authenticationModule.namespace.authenticateUser,
    reads, configurations, requests, response, client, config, context,
  };
}

function assertClient(api, actual, authorization) {
  assert.equal(actual, api.client, 'return the Axios client');
  assert.equal(api.configurations.length, 1);
  const options = api.configurations[0];
  assert.equal(options.baseURL, api.config.APIURL);
  assert.equal(options.timeout, 31000);
  assert.equal(options.headers.Accept, 'application/json');
  assert.equal(options.headers.Authorization, authorization);
  if (authorization === undefined) assert.equal('Authorization' in options.headers, false);
  assert.equal(Object.prototype.hasOwnProperty.call(api.context, 'client'), false, 'do not leak a global client');
}

const tests = [
  ['unavailable storage is not read and still returns a client promise', async () => {
    const api = await loadApi(() => Promise.resolve(false));
    const pending = api.getClient();
    assert.equal(typeof pending.then, 'function');
    const outcome = await pending.then(value => ({ value }), error => ({ error }));
    assert.deepEqual(api.reads, []);
    if (outcome.error) throw outcome.error;
    assertClient(api, outcome.value, undefined);
  }],
  ['waits for availability before reading a saved token', async () => {
    let resolveAvailability;
    const availability = new Promise(resolve => { resolveAvailability = resolve; });
    const api = await loadApi(() => availability, () => Promise.resolve('saved-token'));
    const pending = api.getClient();
    const outcome = pending.then(value => ({ value }), error => ({ error }));
    const readsBeforeAvailability = api.reads.slice();
    resolveAvailability(true);
    const result = await outcome;
    assert.deepEqual(readsBeforeAvailability, []);
    if (result.error) throw result.error;
    assert.deepEqual(api.reads, ['Auth']);
    assertClient(api, result.value, 'Bearer saved-token');
  }],
  ['available storage supplies the saved bearer token', async () => {
    const api = await loadApi(() => Promise.resolve(true), () => Promise.resolve('saved-token'));
    assertClient(api, await api.getClient(), 'Bearer saved-token');
    assert.deepEqual(api.reads, ['Auth']);
  }],
  ['a missing stored token omits Authorization', async () => {
    const api = await loadApi();
    assertClient(api, await api.getClient(), undefined);
    assert.deepEqual(api.reads, ['Auth']);
  }],
  ['an availability rejection propagates without storage access or client creation', async () => {
    const failure = new Error('availability failed');
    const api = await loadApi(() => Promise.reject(failure));
    await assert.rejects(api.getClient(), error => error === failure);
    assert.deepEqual(api.reads, []);
    assert.deepEqual(api.configurations, []);
  }],
  ['a token read rejection propagates without client creation', async () => {
    const failure = new Error('read failed');
    const api = await loadApi(() => Promise.resolve(true), () => Promise.reject(failure));
    await assert.rejects(api.getClient(), error => error === failure);
    assert.deepEqual(api.reads, ['Auth']);
    assert.deepEqual(api.configurations, []);
  }],
  ['authenticateUser reaches the client and returns the login response', async () => {
    const api = await loadApi(() => Promise.resolve(false));
    const result = await api.authenticateUser('person@example.test', 'test-password');
    assert.equal(result, api.response);
    assertClient(api, api.client, undefined);
    assert.equal(api.requests.length, 1);
    assert.equal(api.requests[0].url, '/api/login_check');
    assert.equal(api.requests[0].body.username, 'person@example.test');
    assert.equal(api.requests[0].body.password, 'test-password');
    assert.deepEqual(api.reads, []);
  }],
];

(async () => {
  let failed = 0;
  for (const [name, run] of tests) {
    try { await run(); console.log('PASS:', name); }
    catch (error) { failed++; console.error('FAIL:', name); console.error(error); }
  }
  console.log(`${tests.length - failed}/${tests.length} passed`);
  if (failed) process.exitCode = 1;
})().catch(error => { console.error(error); process.exitCode = 1; });
