import $api from '@api/api';
import {components} from '@api/schema';
import {queryClient} from '@/app.tsx';
import {Button, Group, MultiSelect, Skeleton, TextInput} from '@mantine/core';
import {useForm, zodResolver} from '@mantine/form';
import {notifications} from '@mantine/notifications';
import {createFileRoute, useNavigate} from '@tanstack/react-router'
import {z} from 'zod';
import Roles from '@security/roles';

export const Route = createFileRoute('/users/$id/edit')({
  component: EditArticleLoader,
  beforeLoad: ({context}) => {
    context.auth.isGranted(Roles.USER_UPDATE);
  }
})

const schema = z.object({
  email: z.string().min(3).max(255),
  roles: z.array(z.string()),
});

function EditArticleLoader() {
  const params = Route.useParams();

  const {data: user, isFetching} = $api.useQuery('get', '/api/users/{id}', {
    params: {
      path: {
        id: params.id,
      },
    },
  });

  if (isFetching) {
    return <>
      <Skeleton height={10} width={50} mb={8}/>
      <Skeleton height={35} mb={8}/>
      <Skeleton height={10} mb={8}/>
      <Skeleton height={50} mb={8}/>
      <Skeleton height={35} width={100}/>
    </>;
  }

  return <EditUser userId={params.id} user={user}/>; // TODO: fix type :/
}

function EditUser({userId, user}: { userId: string, user: components["schemas"]["User.jsonld"] }) {
  const navigate = useNavigate();
  const form = useForm({
    initialValues: {
      email: user.email,
      roles: user.roles,
    },
    validate: zodResolver(schema),
  });

  const {data: roles, isFetching: isRolesFetching} = $api.useQuery('get', '/api/users/roles', {}, {
    select: (data): { group: string; items: string[] }[] => {
      const rolesByGroup = Object.entries(data['hydra:member']);
      const groupedRoles: { group: string; items: string[] }[] = rolesByGroup.map(([group, items]) => ({group, items}));
      return groupedRoles;
    },
  });

  const {mutate, isPending: isUserUpdating} = $api.useMutation("patch", "/api/users/{id}", {
    onSuccess: () => {
      notifications.show({
        title: "User updated",
        message: "User updated successfully",
      });
      queryClient.invalidateQueries({queryKey: ["get", "/api/users/{$id}", userId]});
      navigate({to: "/users"})
    },
    onError: (error) => {
      notifications.show({
        title: "Error",
        message: error.detail,
        color: "red",
      });
    },
  });

  return (
    <form onSubmit={form.onSubmit((values) => mutate({params: {path: {id: userId}}, body: values}))}>
      <TextInput
        withAsterisk
        label="Email"
        placeholder="Enter email"
        {...form.getInputProps("email")}
      />
      <Skeleton visible={isRolesFetching}>
        <MultiSelect
          label="Roles"
          placeholder="Select roles"
          data={roles}
          {...form.getInputProps("roles")}
        />
      </Skeleton>
      <Group justify="flex-start" mt="md">
        <Button type="submit" loading={isUserUpdating}>Update</Button>
      </Group>
    </form>
  );
}
