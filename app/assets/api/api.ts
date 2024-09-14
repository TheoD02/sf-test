import createFetchClient, { Middleware } from "openapi-fetch";
import createClient from "openapi-react-query";
import { paths } from "./schema";

const fetchClient = createFetchClient<paths>({
  baseUrl: window.location.origin,
});
const contentTypeBasedOnHttpMethod: Middleware = {
  async onRequest({ request }) {
    if (request.method === "PATCH") {
      request.headers.set("Content-Type", "application/merge-patch+json");
    } else {
      request.headers.set("Content-Type", "application/ld+json");
    }
  },
};

const injectApiToken: Middleware = {
  async onRequest({ request }) {
    const credentials = localStorage.getItem("credentials");

    if (credentials) {
      const { token } = JSON.parse(credentials);
      request.headers.set("Authorization", `Bearer ${token}`);
    }
  }
}

const refreshCredentials: Middleware = {
  async onResponse({ response }) {
    if (response.status === 401) {
      const rememberMe = localStorage.getItem("rememberMe");

      response.clone().json().then((data) => {
        if (data.message.includes("JWT Token") && rememberMe === "true") {
          const credentials = localStorage.getItem("credentials");

          if (credentials) {
            const { refreshToken } = JSON.parse(credentials);
            fetch("/api/auth/refresh", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ refresh_token: refreshToken }),
            }).then((res) => res.json()).then((data) => {
              if (data.token) {
                localStorage.setItem("credentials", JSON.stringify({ token: data.token, refreshToken: data.refresh_token }));
              }
            }).catch((error) => {
              console.error(error);
            });
          }
        } else {
          localStorage.setItem("credentials", JSON.stringify({ token: null, refreshToken: null }));
          if (window.location.pathname !== "/auth/login") {
            window.location.href = "/auth/login";
          }
        }
      });
    }
  }
}

fetchClient.use(contentTypeBasedOnHttpMethod);
fetchClient.use(refreshCredentials);
fetchClient.use(injectApiToken);
const $api = createClient(fetchClient);

export default $api;
