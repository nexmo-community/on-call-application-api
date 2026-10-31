import * as axios from "axios";
import * as SecureStore from 'expo-secure-store'
import configData from "../config.json";

export function getClient() {
    return getApiToken()
      .then(apiToken => {
        return buildClient(apiToken);
      })
  };
  
async function getApiToken() {
    if (await SecureStore.isAvailableAsync()) {
        return SecureStore.getItemAsync('Auth')
        .then(response => {
            return response;
        })
    }
};

function buildClient(apiToken) {
    let headers = {
        Accept: "application/json",
    };

    if (apiToken) {
        headers.Authorization = `Bearer ${apiToken}`;
    }

    const client = axios.create({
        baseURL: configData.APIURL,
        timeout: 31000,
        headers: headers,
    });

    return client;
};