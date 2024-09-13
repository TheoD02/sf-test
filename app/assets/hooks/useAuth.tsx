import $api from "@api/api";
import { components } from "@api/schema";
import { useLocalStorage } from "@mantine/hooks";
import { notifications } from "@mantine/notifications";
import { useQueryClient } from "@tanstack/react-query";
import { createContext, useContext, useState } from "react";
import { useNavigate } from '@tanstack/react-router';

type User = components["schemas"]["User.jsonld"];
const AuthContext = createContext({
  user: null,
  login: (email: string, password: string) => {},
  logout: () => {},
  isLoading: false,
  isError: false,
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const navigate = useNavigate();
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
      navigate({ to: "/" });
    },
    onError: (error) => {
      console.error('Erreur lors de la connexion:', error);
    },
  });

  const { data: userData, isLoading, isError } = $api.useQuery("get", "/api/me", {
    onError: (error: any) => {
      console.error('Erreur lors de la récupération des données utilisateur:', error);
      setToken(null);
    },
  }, {
    enabled: !!token,
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
    navigate({ to: "/" });
  }

  return (
    <AuthContext.Provider value={{ user: userData === undefined ? null : userData, login, logout, isLoading, isError }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
