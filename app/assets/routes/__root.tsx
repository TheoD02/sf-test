import Shell from '@components/shell/Shell'
import { AuthProvider } from '@hooks/useAuth'
import {createRootRoute} from '@tanstack/react-router'

export const Route = createRootRoute({
  component: Root,
})

export default function Root() {
  return (
    <AuthProvider>
      <Shell />
    </AuthProvider>
  );
}
