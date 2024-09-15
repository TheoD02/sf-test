import { Button, Group, Textarea, TextInput } from "@mantine/core";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useForm, zodResolver } from "@mantine/form";
import { z } from "zod";
import $api from "@api/api";
import { notifications } from "@mantine/notifications";
import { queryClient } from "@/app.tsx";
import Roles from "@security/roles";

export const Route = createFileRoute("/users/create")({
  component: CreateArticle,
  beforeLoad: ({ context }) => {
    context.auth.isGranted(Roles.USER_CREATE);
  }
});

const schema = z.object({
  email: z.string().min(3).max(255),
  password: z.string().min(10).max(50),
});

function CreateArticle() {
  const navigate = useNavigate();
  const form = useForm({
    initialValues: {
      email: "",
      password: "",
    },
    validate: zodResolver(schema),
  });

  const { mutate, isPending: isUserSubmitting } = $api.useMutation("post", "/api/users", {
    onSuccess: () => {
      notifications.show({
        title: "User created successfully",
        message: "User has been created successfully",
      });
      navigate({ to: "/users" });
      queryClient.invalidateQueries({ queryKey: ["get", "/api/users"] });
    },
  });

  return (
    <form onSubmit={form.onSubmit((values) => mutate({ body: values }))}>
      <TextInput
        withAsterisk
        label="Email"
        placeholder="Enter email"
        key={form.key("email")}
        {...form.getInputProps("email")}
      />
      <Textarea
        withAsterisk
        label="Password"
        placeholder="Enter password"
        key={form.key("password")}
        {...form.getInputProps("password")}
      />
      <Group justify="flex-start" mt="md">
        <Button type="submit" loading={isUserSubmitting}>Submit</Button>
      </Group>
    </form>
  );
}
