import $api from "@api/api";
import { components } from "@api/schema";
import { useLocalStorage } from "@mantine/hooks";
import { notifications } from "@mantine/notifications";
import { useQueryClient } from "@tanstack/react-query";
import { createContext, useContext, useEffect, useState } from "react";
import { invariant, redirect } from '@tanstack/react-router';
import Roles from "@security/roles";
import { useLoading } from "./useLoading";

type User = components["schemas"]["User.jsonld"];
type Context = {
  user: User | null;
  login: (email: string, password: string) => void;
  logout: () => void;
  isLoading: boolean;
  isError: boolean;
  isGranted: (roles: Roles[] | Roles, redirectToLogin: boolean) => boolean;
  isAuthenticated: boolean;
  shouldWaitForAuthentification: boolean;
};
export type AuthContext = Context;

const AuthContext = createContext<Context>({
  user: null,
  login: (email: string, password: string) => {},
  logout: () => {},
  isLoading: false,
  isError: false,
  isGranted: (roles: Roles[] | Roles, redirectToLogin: boolean) => false,
  isAuthenticated: false,
  shouldWaitForAuthentification: false,
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const {setIsLoading, setReason} = useLoading();
  const [token, setToken] = useLocalStorage({
    key: "token",
    defaultValue: null,
  });
  const queryClient = useQueryClient();

  useEffect(() => {
    if (token !== null && token !== 'null') {
      setIsLoading(true);
      setReason('Sorry for the wait, we are checking your access...');
    }
  }, [token]);

  // Mutation pour le login
  const { mutate: loginMutation } = $api.useMutation("post", "/auth/login", {
    onSuccess: async (data) => {
      setToken(data.token);
      queryClient.invalidateQueries({ queryKey: ["get", "/api/me"] });
      notifications.show({
        title: "Logged in",
        message: "You have been successfully logged in",
        color: "green",
      });
    },
  });

  const { data: userData, isLoading, isError } = $api.useQuery("get", "/api/me", {}, {
    enabled: !!token,
    refetchInterval: 15 * 60 * 1000, // 15 minutes keep user fresh
  });

  useEffect(() => {
    if (userData !== undefined) {
      setIsLoading(false);
    }
  }, [userData]);

  const login = (email: string, password: string) => loginMutation({ body: { email, password } });
  const logout = () => {
    setToken(null);
    queryClient.clear(); // Nettoie le cache
    notifications.show({
      title: "Logged out",
      message: "You have been successfully logged out",
      color: "green",
    });
  }
  const isAuthenticated = userData !== undefined && userData !== null && token !== null;
  const shouldWaitForAuthentification = localStorage.getItem("token") !== 'null' && (userData === undefined || userData === null);

  const isGranted = (roles: Roles | Roles[], redirectToLogin = true) => {
    if (shouldWaitForAuthentification === false && !isAuthenticated) {
      throw redirect({ to: "/auth/login" });
    }

    if (!Array.isArray(roles)) {
      roles = [roles];
    }

    const isGranted = userData !== undefined ? (userData?.roles || []).some((role) => roles.includes(role)) : false;

    if (!isGranted && redirectToLogin) {
      throw redirect({ to: "/error/403" });
    }

    return isGranted;
  }


  return (
    <AuthContext.Provider value={{ user: userData === undefined ? null : userData, login, logout, isLoading, isError, isGranted, isAuthenticated, shouldWaitForAuthentification }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}

export function useMe() {
  const { user } = useAuth();
  return user;
}
