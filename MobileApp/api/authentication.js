import { getClient } from "./client.js";

export function authenticateUser(email, password) {
  return getClient()
    .then(client => {
      return client.post('/api/login_check', {
        username: email,
        password: password
      });
    });
};