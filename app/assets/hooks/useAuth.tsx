import $api from "@api/api";
import {components} from "@api/schema";
import {useLocalStorage} from "@mantine/hooks";
import {createContext, ReactNode, useEffect, useState} from "react";

type User = components["schemas"]["User.jsonld"];

interface AuthContextType {
  user: User | null;
  token: string | null;
  login: (jwtToken: null | string, user: User) => void;
  logout: () => void;
  isAuthenticating: boolean;
}

export const AuthContext = createContext<AuthContextType>({
  user: null,
  token: null,
  login: (jwtToken: null | string, user: User) => {
    console.log("You should implement login");
  },
  logout: () => {
    console.log("You should implement logout");
  },
  isAuthenticating: false,
});

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({children}: AuthProviderProps) => {
  const {data: loggedInUser, isLoading: isLoadingLoggedInUser} =
    $api.useQuery(
      "get",
      "/api/me",
      {},
      {throwOnError: false, initialData: null, retry: false}
    );

  const {mutate: logoutApi} = $api.useMutation("get", "/api/logout", {
    onSettled: () => {
      window.location.href = "/";
    },
  });
  const [user, setUser, removeUser] = useLocalStorage<User | null>({
    key: "user",
    defaultValue: loggedInUser,
  });
  const [token, setToken, removeToken] = useLocalStorage<string | null>({
    key: "token",
    defaultValue: null,
  });
  const [isAuthenticating, setIsAuthenticating] = useState(!(user || token));

  const login = (jwtToken: null | string, user: User) => {
    setUser(user);
    setToken(jwtToken);
    setIsAuthenticating(false);
  };

  useEffect(() => {
    login(null, loggedInUser);
  }, [loggedInUser]);

  const logout = () => {
    if (token === null) {
      logoutApi({});
    }
    removeToken();
    removeUser();
  };

  if (isLoadingLoggedInUser) {
    return <div>Loading...</div>;
  }

  return (
    <AuthContext.Provider
      value={{user, token, login, logout, isAuthenticating}}
    >
      {children}
    </AuthContext.Provider>
  );
};
