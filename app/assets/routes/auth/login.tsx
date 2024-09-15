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
import { useForm } from "@mantine/form";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { useAuth } from "@hooks/useAuth";
import { notifications } from "@mantine/notifications";
import { useState } from "react";
import { set } from "zod";

export const Route = createFileRoute("/auth/login")({
  component: Login,
  beforeLoad: ({ context }) => {
    console.log(context.auth);
    if (context.auth?.user !== null) {
      throw redirect({ to: "/" });
    }
  }
});

function Login() {
  const { login, isLoading } = useAuth();
  const navigate = useNavigate();
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const form = useForm({
    initialValues: {
      // TODO: Should not be set but for dev is good enough for now
      email: "admin@domain.tld",
      password: "admin",
    },
  });

  const handleLogin = ({ email, password }: { email: string, password: string }) => {
    login(
      { body: { email, password } },
      {
        onSuccess: () => navigate({ to: "/" }),
        onError: () => {
          notifications.show({
            title: "Login failed",
            message: "Please check your credentials",
            color: "red",
          });
          setErrorMessage("Invalid credentials");
        }
      });
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

      <form onSubmit={form.onSubmit((values) => handleLogin(values))}>
        <Paper withBorder shadow="md" p={30} mt={30} radius="md">
          {errorMessage && (
            <Text c="red" ta="center" size="sm" mt={-10} mb={10}>
              {errorMessage}
            </Text>
          )}
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
            <Checkbox label="Remember me" onClick={(e) => localStorage.setItem("rememberMe", e.target.checked)} />
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
