import $api from "@api/api";
import { components } from "@api/schema";
import { useLocalStorage } from "@mantine/hooks";
import { notifications } from "@mantine/notifications";
import { useQueryClient } from "@tanstack/react-query";
import { createContext, useContext, useState } from "react";
import { useNavigate, redirect } from '@tanstack/react-router';
import {jwtDecode, JwtPayload } from 'jwt-decode';
import Roles from "@security/roles";
import { boolean } from "zod";

type User = components["schemas"]["User.jsonld"];
type Context = {
  user: User | null;
  login: (email: string, password: string) => void;
  logout: () => void;
  isLoading: boolean;
  isError: boolean;
  isGranted: (roles: Roles[] | Roles, redirectToLogin: boolean) => boolean;
  isAuthenticated: boolean;
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
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useLocalStorage({
    key: "token",
    defaultValue: null,
  });
  const queryClient = useQueryClient();

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

  const { data: userData, isLoading, isError } = $api.useQuery("get", "/api/me", {
    onError: (error: any) => {
      setToken(null); // TODO: Maybe hard to set token to null here, should check the reason of the error
    },
  }, {
    enabled: !!token,
    refetchInterval: 15 * 60 * 1000, // 15 minutes keep user fresh
  });

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

  const isGranted = (roles: Roles | Roles[], redirectToLogin = true) => {
    if (!userData) {
      if (redirectToLogin) {
        throw redirect({ to: "/auth/login" });
      }
      return false;
    }

    if (!Array.isArray(roles)) {
      roles = [roles];
    }

    const isGranted = userData?.roles.some((role) => roles.includes(role));

    if (!isGranted && redirectToLogin) {
      throw redirect({ to: "/error/403" });
    }

    return isGranted;
  }

  const isAuthenticated = userData !== undefined && userData !== null;

  return (
    <AuthContext.Provider value={{ user: userData === undefined ? null : userData, login, logout, isLoading, isError, isGranted, isAuthenticated }}>
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
