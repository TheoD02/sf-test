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
    } else {
      if (window.location.pathname !== "/auth/login") {
        window.location.href = "/auth/login";
        return;
      }
    }
  },
};

const refreshTokenMiddleware: Middleware = {
  async onResponse({ response, request }) {
    if (response.status === 401) {
      const credentials = localStorage.getItem("credentials");
      if (request.url.includes("/auth/login")) {
        return response; // Do nothing user just failed to login :)
      }

      const rememberMe = localStorage.getItem("rememberMe") === 'true';

      if (!rememberMe) {
        localStorage.removeItem("credentials");
        if (window.location.pathname !== "/auth/login") {
          window.location.href = "/auth/login";
        }
        return response
      }

      if (credentials) {
        const { refreshToken } = JSON.parse(credentials);

        const refreshResponse = await fetch("/api/auth/refresh", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ refresh_token: refreshToken }),
        });

        if (refreshResponse.ok) {
          const newTokens = await refreshResponse.json();
          localStorage.setItem(
            "credentials",
            JSON.stringify({
              token: newTokens.token,
              refreshToken: newTokens.refresh_token,
            })
          );

          // Retry the original request with the new token
          request.headers.set("Authorization", `Bearer ${newTokens.token}`);
        } else {
          // If refresh fails, redirect to login
          localStorage.removeItem("credentials");
          if (window.location.pathname !== "/auth/login") {
            window.location.href = "/auth/login";
          }
        }
      }
    }
    return response;
  },
};

fetchClient.use(contentTypeBasedOnHttpMethod);
fetchClient.use(injectApiToken);
fetchClient.use(refreshTokenMiddleware);

const $api = createClient(fetchClient);

export default $api;
