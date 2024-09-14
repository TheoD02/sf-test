import Error from '@components/security/Error'
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/error/403')({
  component: () => <Error code={403} label='Forbidden' description='You do not have permission to access this resource' />,
})
