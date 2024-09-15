import Shell from '@components/shell/Shell'
import { AuthContext } from '@hooks/useAuth'
import { createRootRouteWithContext } from '@tanstack/react-router'
import { AuthProvider } from '../hooks/useAuth';

interface MyRouterContext {
  // The ReturnType of your useAuth hook or the value of your AuthContext
  auth: AuthContext
}

export const Route = createRootRouteWithContext<MyRouterContext>()({
  component: () => (
    <AuthProvider>
      <Shell />
    </AuthProvider>
  ),
})
