import { useAuth } from "@hooks/useAuth";
import Roles from "@security/roles";

type Props = {
  roles: Roles[];
};

export default function PrivateComponent({
  roles,
}: Props) {
  const { user } = useAuth();
  console.log(user);

  if (roles.includes(Roles.ROLE_USER)) {
    return <div>User Component</div>;
  }

  return <div>Private Component</div>;
}
