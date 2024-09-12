import createFetchClient, {Middleware} from "openapi-fetch";
import createClient from "openapi-react-query";
import {paths} from "./schema";

const fetchClient = createFetchClient<paths>({
  baseUrl: window.location.origin,
});
const contentTypeBasedOnHttpMethod: Middleware = {
  async onRequest({request}) {
    if (request.method === "PATCH") {
      request.headers.set("Content-Type", "application/merge-patch+json");
    } else {
      request.headers.set("Content-Type", "application/ld+json");
    }
  },
};

const injectApiToken: Middleware = {
  async onRequest({request}) {
    const token = localStorage.getItem("token");

    if (token && token !== "null") {
      request.headers.set("Authorization", `Bearer ${token.replaceAll('"', '')}`)
    }
  }
}

fetchClient.use(contentTypeBasedOnHttpMethod);
fetchClient.use(injectApiToken);
const $api = createClient(fetchClient);

export default $api;
