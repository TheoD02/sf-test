import { Center, Loader, MantineProvider, Transition } from "@mantine/core";
import { RouterProvider, createRouter } from "@tanstack/react-router";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { notifications, Notifications } from "@mantine/notifications";
import { ModalsProvider } from "@mantine/modals";

// Import the generated route tree
import { routeTree } from "./routeTree.gen";
import { AuthProvider, useAuth } from "./hooks/useAuth";
import { LoadingProvider, useLoading } from "@hooks/useLoading";
import { useEffect } from "react";
import { useNetwork } from "@mantine/hooks";

// Create a new router instance
const router = createRouter({ routeTree });

// Register the router instance for type safety
declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
  }
}

export const queryClient = new QueryClient();

function InnerApp() {
  const auth = useAuth();
  const network = useNetwork();

  useEffect(() => {
    if (network.online === false) {
      notifications.show({
        title: "You seem to be offline",
        message: "Please check your internet connection",
        color: "red",
      });
    }
  }, [network.online]);

  return <RouterProvider router={router} context={{ auth }} />;
}

export default function App() {
  return (
    <MantineProvider defaultColorScheme="dark">
      <LoadingProvider>
        <QueryClientProvider client={queryClient}>
          <AuthProvider>
            <ModalsProvider>
              <Notifications position="top-right" />
              <InnerApp />
            </ModalsProvider>
          </AuthProvider>
        </QueryClientProvider>
      </LoadingProvider>
    </MantineProvider>
  );
}
