// Run with: node --experimental-vm-modules MobileApp/tests/login.test.cjs
// Uses the project's @babel/core dependency to omit only render(), not login.
// This is an isolated class-method regression, not an Expo/native UI test.
const assert = require('assert').strict;
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const babel = require('@babel/core');

const filename = path.join(__dirname, '../components/LoginScreen.js');
const source = fs.readFileSync(filename, 'utf8');
let omittedRenders = 0;
const { code } = babel.transformSync(source, {
  configFile: false,
  babelrc: false,
  parserOpts: { plugins: ['jsx', 'classProperties'] },
  plugins: [({ types }) => ({ visitor: {
    ClassMethod(method) {
      if (method.node.key.name === 'render' &&
          method.parentPath.parent.id.name === 'LoginScreen') {
        method.get('body').replaceWith(types.blockStatement([]));
        omittedRenders++;
      }
    },
  } })],
});
assert.equal(omittedRenders, 1, 'omit the render method only; do not transform login');

const settle = () => new Promise(resolve => setImmediate(resolve));

async function loadLogin({
  available = () => Promise.resolve(true),
  save = () => Promise.resolve(),
  authenticate = () => Promise.resolve({ data: { token: 'saved-token' } }),
} = {}) {
  const writes = [];
  const requests = [];
  const logs = [];
  const availabilityCalls = [];
  const updates = [];
  const context = vm.createContext({ console: { log: (...args) => logs.push(args) } });
  class Component {
    setState(update) { updates.push(update); this.state = { ...this.state, ...update }; }
  }
  function dependency(exports) {
    return new vm.SyntheticModule(Object.keys(exports), function () {
      for (const name of Object.keys(exports)) this.setExport(name, exports[name]);
    }, { context });
  }
  const dependencies = {
    react: dependency({ default: {}, Component }),
    'react-native': dependency({ StyleSheet: { create: styles => styles },
      Text: {}, TextInput: {}, TouchableOpacity: {}, View: {} }),
    'expo-secure-store': dependency({
      isAvailableAsync() {
        availabilityCalls.push(true);
        const result = available();
        // The baseline ignores this promise. Observe rejection to keep the
        // runner alive, but return that same promise to the production method.
        result.catch(() => {});
        return result;
      },
      setItemAsync(...args) { writes.push(args); return save(...args); },
    }),
    '../api/authentication.js': dependency({
      authenticateUser(...args) { requests.push(args); return authenticate(...args); },
    }),
  };
  const module = new vm.SourceTextModule(code, { context, identifier: filename });
  await module.link(name => {
    assert.ok(Object.hasOwn(dependencies, name), `unexpected dependency: ${name}`);
    return dependencies[name];
  });
  await module.evaluate();
  const screen = new module.namespace.default();
  screen.handleEmail('person@example.test');
  screen.handlePassword('test-password');
  return { screen, writes, requests, logs, availabilityCalls, updates };
}

const tests = [
  ['unavailable SecureStore does not receive a token write', async () => {
    const app = await loadLogin({ available: () => Promise.resolve(false) });
    app.screen.login();
    await settle();
    assert.deepEqual(app.requests, [['person@example.test', 'test-password']]);
    assert.equal(app.availabilityCalls.length, 1);
    assert.deepEqual(app.writes, []);
    assert.deepEqual(app.logs, []);
  }],
  ['a pending availability result gates the write and success message', async () => {
    let resolve;
    const pending = new Promise(done => { resolve = done; });
    const app = await loadLogin({ available: () => pending });
    app.screen.login();
    await settle();
    const earlyWrites = app.writes.slice();
    const earlyLogs = app.logs.slice();
    resolve(true);
    await settle();
    assert.deepEqual(earlyWrites, []);
    assert.deepEqual(earlyLogs, []);
    assert.deepEqual(app.writes, [['Auth', 'saved-token']]);
    assert.deepEqual(app.logs, [['You Successfully logged in!']]);
  }],
  ['availability rejection is handled without writing or reporting success', async () => {
    const error = new Error('availability failed');
    const app = await loadLogin({ available: () => Promise.reject(error) });
    app.screen.login();
    await settle();
    assert.deepEqual(app.writes, []);
    assert.deepEqual(app.logs, [[error]]);
  }],
  ['available storage retains the Auth key and token', async () => {
    const app = await loadLogin();
    app.screen.login();
    await settle();
    assert.deepEqual(app.writes, [['Auth', 'saved-token']]);
    assert.deepEqual(app.logs, [['You Successfully logged in!']]);
  }],
  ['success is reported only after the token write completes', async () => {
    let resolve;
    const pending = new Promise(done => { resolve = done; });
    const app = await loadLogin({ save: () => pending });
    app.screen.login();
    await settle();
    assert.deepEqual(app.writes, [['Auth', 'saved-token']]);
    assert.deepEqual(app.logs, []);
    resolve();
    await settle();
    assert.deepEqual(app.logs, [['You Successfully logged in!']]);
  }],
  ['token write rejection retains the existing error path', async () => {
    const error = new Error('write failed');
    const app = await loadLogin({ save: () => Promise.reject(error) });
    app.screen.login();
    await settle();
    assert.deepEqual(app.writes, [['Auth', 'saved-token']]);
    assert.deepEqual(app.logs, [[error]]);
  }],
  ['authentication rejection never accesses SecureStore', async () => {
    const error = new Error('authentication failed');
    const app = await loadLogin({ authenticate: () => Promise.reject(error) });
    app.screen.login();
    await settle();
    assert.deepEqual(app.availabilityCalls, []);
    assert.deepEqual(app.writes, []);
    assert.deepEqual(app.logs, [[error]]);
  }],
  ['a missing token keeps the existing error-state update without storage access', async () => {
    const app = await loadLogin({ authenticate: () => Promise.resolve({ data: {}, message: 'try again' }) });
    app.screen.login();
    await settle();
    assert.equal(app.screen.state.errorText, 'There was an error: try again');
    assert.equal(app.updates.at(-1).errorText, 'There was an error: try again');
    assert.deepEqual(app.availabilityCalls, []);
    assert.deepEqual(app.writes, []);
  }],
  ['overlapping logins keep each response paired with its own availability result', async () => {
    const resolve = [];
    const pending = [0, 1].map(i => new Promise(done => { resolve[i] = done; }));
    let call = 0;
    const app = await loadLogin({
      authenticate: email => Promise.resolve({ data: { token: email } }),
      available: () => pending[call++],
    });
    app.screen.login();
    app.screen.handleEmail('second@example.test');
    app.screen.login();
    await settle();
    const earlyWrites = app.writes.slice();
    resolve[1](true);
    await settle();
    resolve[0](false);
    await settle();
    assert.deepEqual(earlyWrites, []);
    assert.deepEqual(app.requests, [
      ['person@example.test', 'test-password'], ['second@example.test', 'test-password'],
    ]);
    assert.deepEqual(app.writes, [['Auth', 'second@example.test']]);
    assert.deepEqual(app.logs, [['You Successfully logged in!']]);
    assert.equal(app.screen.state.email, 'second@example.test');
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
