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
} from "@mantine/core";
import {useForm} from "@mantine/form";
import {createFileRoute, useNavigate} from "@tanstack/react-router";
import { useAuth } from "@hooks/useAuth";

export const Route = createFileRoute("/auth/login")({
  component: Login,
});

function Login() {
  const navigate = useNavigate();
  const { login, isLoading, isAuthenticated, shouldWaitForAuthentification } = useAuth();


  const form = useForm({
    initialValues: {
      // TODO: Should not be set but for dev is good enough for now
      email: "admin@domain.tld",
      password: "admin",
    },
  });

  if (isLoading || shouldWaitForAuthentification) {
    return <Text>Loading...</Text>;
  }

  if (isAuthenticated) {
    navigate({ to: "/" });
    return;
  }

  return (
    <Container size={420} my={40}>
      <Title ta="center">Welcome back!</Title>
      <Text c="dimmed" size="sm" ta="center" mt={5}>
        Do not have an account yet?{" "}
        <Anchor size="sm" component="button">
          Create account
        </Anchor>
      </Text>

      <form onSubmit={form.onSubmit((values) => login(values.email, values.password))}>
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
          <Button type="submit" fullWidth mt="xl" loading={isLoading}>
            Sign in
          </Button>
        </Paper>
      </form>
    </Container>
  );
}
