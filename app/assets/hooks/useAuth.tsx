import $api from "@api/api";
import { components } from "@api/schema";
import { useLocalStorage } from "@mantine/hooks";
import { notifications } from "@mantine/notifications";
import Roles from "@security/roles";
import { useQueryClient } from "@tanstack/react-query";
import { redirect, useNavigate, useRouteContext, useRouter } from "@tanstack/react-router";
import { createContext, useContext, useEffect } from "react";

type User = components["schemas"]["User.jsonld"];
type Context = {
  user: User | null;
  login: (email: string, password: string) => void;
  logout: () => void;
  isLoading: boolean;
  isError: boolean;
  isGranted: (roles: Roles | Roles[], autoRedirect?: boolean) => boolean;
};
export type AuthContext = Context;

const AuthContext = createContext<Context>({
  user: null,
  login: (email: string, password: string) => {},
  logout: () => {},
  isLoading: false,
  isError: false,
  isGranted: () => false,
});

type Credentials = {
  token: string | null;
  refreshToken: string | null;
  state: "loggedOut" | "loggedIn";
};

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [credentials, setCredentials] = useLocalStorage<Credentials>({
    key: "credentials",
    defaultValue: { token: null, refreshToken: null, state: "loggedOut" },
  });
  const queryClient = useQueryClient();
  const router = useRouter();

  const { mutate: loginMutation, isPending: isLoginLoading } = $api.useMutation("post", "/auth/login", {
    onSuccess: async (data) => {
      setCredentials({ token: data.token, refreshToken: data.refresh_token, state: "loggedIn" });
      queryClient.invalidateQueries({ queryKey: ["get", "/api/me"] });
      notifications.show({
        title: "Logged in",
        message: "You have been successfully logged in",
        color: "green",
      });
    },
  });

  const { data: userData, isFetching: isMeLoading, isError } = $api.useQuery("get", "/api/me", {}, {
    enabled: !!credentials.token,
    refetchInterval: 15 * 60 * 1000, // 15 minutes keep user fresh
  });

  const logout = () => {
    setCredentials({ token: null, refreshToken: null, state: "loggedOut" });
    queryClient.clear();
    notifications.show({
      title: "Logged out",
      message: "You have been successfully logged out",
      color: "green",
    });
  }

  const isGranted = (roles: Roles | Roles[], autoRedirect = true) => {
    if (userData === undefined) {
      return false;
    }

    if (!Array.isArray(roles)) {
      roles = [roles];
    }

    const isGranted = roles.some((role) => userData.roles.includes(role));

    if (!isGranted && autoRedirect) {
      throw redirect({ to: "/error/403" });
    }

    return isGranted;
  }

  const context = { user: userData === undefined ? null : userData, login: loginMutation, logout, isLoading: isLoginLoading || isMeLoading, isError, isGranted };
  useEffect(() => {
    router.options.context.auth = context;
  }, [context]);

  return (
    <AuthContext.Provider value={context}>
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
