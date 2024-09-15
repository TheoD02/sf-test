import { useAuth } from "@hooks/useAuth";
import Roles from "@security/roles";

type Props = {
  roles: Roles[];
  children: React.ReactNode;
};

export default function PrivateComponent({
  roles,
  children,
}: Props) {
  const { isGranted } = useAuth();

  if (!isGranted(roles, false)) {
    return null;
  }

  return <>{children}</>;
}
