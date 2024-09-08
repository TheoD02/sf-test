import {Button, Group, Textarea, TextInput} from "@mantine/core";
import {createLazyFileRoute, useNavigate} from "@tanstack/react-router";
import {useForm, zodResolver} from "@mantine/form";
import {z} from "zod";
import $api from "@api/api";
import {notifications} from "@mantine/notifications";
import {queryClient} from "@app";

export const Route = createLazyFileRoute("/articles/create")({
  component: CreateArticle,
});

const schema = z.object({
  title: z.string().min(3).max(255),
  content: z.string().min(10).max(10000),
});

function CreateArticle() {
  const navigate = useNavigate();
  const form = useForm({
    initialValues: {
      title: "",
      content: "",
    },

    validate: zodResolver(schema),
  });

  const {mutate, isPending: isArticleSubmitting} = $api.useMutation("post", "/api/articles", {
    onSuccess: () => {
      notifications.show({
        title: "Article created successfully",
        message: "Check your email for the confirmation link",
      });
      navigate({to: "/articles"});
      queryClient.invalidateQueries({queryKey: ["get", "/api/articles"]});
    },
  });

  return (
    <form onSubmit={form.onSubmit((values) => mutate({body: values}))}>
      <TextInput
        withAsterisk
        label="Title"
        placeholder="Enter title"
        {...form.getInputProps("title")}
      />
      <Textarea
        withAsterisk
        label="Content"
        placeholder="Enter content"
        {...form.getInputProps("content")}
      />
      <Group justify="flex-start" mt="md">
        <Button type="submit" loading={isArticleSubmitting}>Submit</Button>
      </Group>
    </form>
  );
}
