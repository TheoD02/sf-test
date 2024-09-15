import { createFileRoute } from "@tanstack/react-router";
import PrivateComponent from "@components/security/PrivateComponent";
import Roles from "@security/roles";

export const Route = createFileRoute("/")({
  component: () => {
    return <div>
      <h1>Hello World</h1>
      <PrivateComponent roles={[Roles.USER_GET_COLLECTION]}>
        <p>Only users with USER_GET_COLLECTION role can see this</p>
      </PrivateComponent>
    </div>;
  },
});
