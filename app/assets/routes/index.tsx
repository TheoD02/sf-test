import { createFileRoute } from "@tanstack/react-router";
import PrivateComponent from "@components/security/PrivateComponent";
import Roles from "@security/roles";
export const Route = createFileRoute("/")({
  component: () => {
    return <div>
      <h1>Hello World</h1>

      <PrivateComponent roles={[Roles.ROLE_ADMIN]}/>
    </div>;
  },
});
