import $api from '@api/api';
import {components} from '@api/schema';
import {queryClient} from '@/app.tsx';
import {Button, Group, Skeleton, Textarea, TextInput} from '@mantine/core';
import {useForm, zodResolver} from '@mantine/form';
import {notifications} from '@mantine/notifications';
import {createLazyFileRoute, useNavigate} from '@tanstack/react-router'
import {z} from 'zod';

export const Route = createLazyFileRoute('/users/$id/edit')({
  component: EditArticleLoader,
})

const schema = z.object({
  title: z.string().min(3).max(255),
  content: z.string().min(10).max(10000),
});

function EditArticleLoader() {
  const params = Route.useParams();

  const {data: article, isFetching} = $api.useQuery('get', '/api/articles/{id}', {
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

  return <EditArticle articleId={params.id} article={article}/>; // TODO: fix type :/
}

function EditArticle({articleId, article}: { articleId: string, article: components["schemas"]["Article.jsonld"] }) {
  const navigate = useNavigate();
  const form = useForm({
    initialValues: {
      title: article.title,
      content: article.content,
    },

    validate: zodResolver(schema),
  });

  const {mutate, isPending: isArticleUpdating} = $api.useMutation("patch", "/api/articles/{id}", {
    onSuccess: () => {
      notifications.show({
        title: "Article updated",
        message: "Article updated successfully",
      });
      navigate({to: "/articles"})
      queryClient.invalidateQueries({queryKey: ["get", "/api/articles/{$id}", articleId]});
    },
  });

  return (
    <form onSubmit={form.onSubmit((values) => mutate({body: values, params: {path: {id: articleId}}}))}>
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
        <Button type="submit" loading={isArticleUpdating} dela>Update</Button>
      </Group>
    </form>
  );
}
