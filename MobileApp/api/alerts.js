import { getClient } from "./client.js";

export function getAlerts() {
  return getClient()
    .then(function(client) {
      return client.get("/api/alerts");
    });
};

export function acceptAlert(alertId) {
  return getClient()
    .then(function(client) {
      return client.post(`/api/alerts/${alertId}/accept`);
    });
};

export function cancelAlert(alertId) {
  return getClient()
    .then(function(client) {
      return client.post(`/api/alerts/${alertId}/cancel`);
    });
};

export function completeAlert(alertId) {
  return getClient()
    .then(function(client) {
      return client.post(`/api/alerts/${alertId}/complete`);
    })
};