import {AuthContext} from "@hooks/useAuth";
import {
  Anchor,
  Button,
  Checkbox,
  Container,
  Group,
  Paper,
  Text,
  PasswordInput,
  TextInput,
  Title,
  ActionIcon,
} from "@mantine/core";
import {useForm} from "@mantine/form";
import {notifications} from "@mantine/notifications";
import {IconBrandSpotifyFilled} from "@tabler/icons-react";
import {createFileRoute, useNavigate} from "@tanstack/react-router";
import {useContext} from "react";
import $api from '@api/api';

export const Route = createFileRoute("/auth/login")({
  component: Login,
});

function Login() {
  const navigate = useNavigate();
  const {login} = useContext(AuthContext);
  const form = useForm({
    initialValues: {
      // TODO: Should not be set but for dev is good enough for now
      email: "admin@domain.tld",
      password: "admin",
    },
  });

  const {mutate, isPending: isLoginPending} = $api.useMutation(
    "post",
    "/auth",
    {
      onSuccess: (data) => {
        login(data.token, form.getValues());
        notifications.show({
          title: "Login successful",
          message: "You are now logged in",
          color: "green",
        });
        navigate({to: "/"});
      },
    }
  );

  return (
    <Container size={420} my={40}>
      <Title ta="center">Welcome back!</Title>
      <Text c="dimmed" size="sm" ta="center" mt={5}>
        Do not have an account yet?{" "}
        <Anchor size="sm" component="button">
          Create account
        </Anchor>
      </Text>

      <form onSubmit={form.onSubmit((values) => mutate({body: values}))}>
        <Paper withBorder shadow="md" p={30} mt={30} radius="md">
          <TextInput
            label="Email"
            placeholder="you@mantine.dev"
            required
            {...form.getInputProps("email")}
          />
          <PasswordInput
            label="Password"
            placeholder="Your password"
            required
            mt="md"
            {...form.getInputProps("password")}
          />
          <Group justify="space-between" mt="lg">
            <Checkbox label="Remember me"/>
            <Anchor component="button" size="sm">
              Forgot password?
            </Anchor>
          </Group>
          <Button type="submit" fullWidth mt="xl" loading={isLoginPending}>
            Sign in
          </Button>
        </Paper>
      </form>
    </Container>
  );
}
