import {MantineProvider} from "@mantine/core";
import {RouterProvider, createRouter} from "@tanstack/react-router";
import {QueryClient, QueryClientProvider} from "@tanstack/react-query";
import {Notifications} from "@mantine/notifications";
import {ModalsProvider} from "@mantine/modals";

// Import the generated route tree
import {routeTree} from "./routeTree.gen";
import {AuthProvider} from "./hooks/useAuth";

// Create a new router instance
const router = createRouter({routeTree});

// Register the router instance for type safety
declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
  }
}

export const queryClient = new QueryClient();

export default function App() {
  return (
    <MantineProvider defaultColorScheme="dark">
      <QueryClientProvider client={queryClient}>
        <ModalsProvider>
            <Notifications position="top-right"/>
            <RouterProvider router={router}/>
        </ModalsProvider>
      </QueryClientProvider>
    </MantineProvider>
  );
}
