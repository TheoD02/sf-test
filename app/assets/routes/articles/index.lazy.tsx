import {createLazyFileRoute, Link, useNavigate} from "@tanstack/react-router";
import $api from "@api/api";
import {
  MantineReactTable,
  MRT_ColumnFiltersState,
  useMantineReactTable,
  type MRT_ColumnDef,
} from "mantine-react-table";
import {useMemo, useState} from "react";
import {components} from "@api/schema";
import {ActionIcon, Text, Button, Container, Group} from "@mantine/core";
import {IconEdit, IconTrash} from "@tabler/icons-react";
import {modals} from "@mantine/modals";
import {notifications} from "@mantine/notifications";
import { queryClient } from '../../app';

export const Route = createLazyFileRoute("/articles/")({
  component: Articles,
});

function removeEmptyValues(object: any): any {
  // TODO: Common in helpers
  return Object.fromEntries(Object.entries(object).filter(([_, v]) => v));
}

function Articles() {
  const [pagination, setPagination] = useState({
    pageIndex: 0,
    pageSize: 30,
  });
  const [columnFilters, setColumnFilters] = useState<MRT_ColumnFiltersState>(
    []
  );
  const {data: articles, isFetching} = $api.useQuery("get", "/api/articles", {
    params: {
      query: removeEmptyValues({
        // Maybe we can do that directly in querySerializer of client ?
        page: pagination.pageIndex + 1,
        id: columnFilters.find((f) => f.id === "id")?.value ?? "",
        title: columnFilters.find((f) => f.id === "title")?.value ?? "",
        content: columnFilters.find((f) => f.id === "content")?.value ?? "",
      }),
    },
  });

  const columns = useMemo<
    MRT_ColumnDef<components["schemas"]["Article.jsonld"]>[]
  >(
    () => [
      {
        accessorKey: "id",
        header: "ID",
      },
      {
        accessorKey: "title",
        header: "Title",
      },
      {
        accessorKey: "content",
        header: "Content",
      },
    ],
    []
  );
  const navigate = useNavigate();
  const {mutate: deleteArticle} = $api.useMutation(
    "delete",
    "/api/articles/{id}",
    {
      onSuccess: () => {
        notifications.show({
          title: "Article deleted",
          message: "Article deleted successfully",
        });
        queryClient.invalidateQueries({
          queryKey: ["get", "/api/articles"],
        });
      },
    }
  );

  const table = useMantineReactTable({
    columns,
    data: articles?.["hydra:member"] ?? [],
    state: {isLoading: isFetching, pagination, columnFilters},
    initialState: {density: "xs"},
    onPaginationChange: setPagination,
    manualPagination: true,
    rowCount: articles?.["hydra:totalItems"] ?? 0,
    manualFiltering: true,
    onColumnFiltersChange: setColumnFilters,
    enableRowActions: true,
    renderRowActions: ({row}) => (
      <Group align="center" justify="flex-end">
        <ActionIcon
          color="orange"
          onClick={() =>
            navigate({
              to: `/articles/$id/edit`,
              params: {id: row.original.id?.toString() ?? ""},
            })
          }
        >
          <IconEdit/>
        </ActionIcon>

        <ActionIcon
          color="red"
          onClick={() =>
            modals.openConfirmModal({
              title: "Delete Article",
              children: (
                <Text>Are you sure you want to delete this article?</Text>
              ),
              onConfirm: () => {
                deleteArticle({
                  params: {
                    path: {
                      id: row.original.id?.toString() ?? "",
                    },
                  },
                });
              },
              labels: {confirm: "Delete", cancel: "Cancel"},
            })
          }
        >
          <IconTrash/>
        </ActionIcon>
      </Group>
    ),
  });

  return (
    <Container fluid>
      <Group pb={10}>
        <Button component={Link} to="/articles/create">
          Create
        </Button>
      </Group>
      <MantineReactTable table={table}/>
    </Container>
  );
}
