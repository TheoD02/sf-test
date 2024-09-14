import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import $api from "@api/api";
import {
  MantineReactTable,
  MRT_ColumnFiltersState,
  useMantineReactTable,
  type MRT_ColumnDef,
} from "mantine-react-table";
import { useMemo, useState } from "react";
import { components } from "@api/schema";
import { ActionIcon, Box, Button, Center, Container, Group, Menu, Text } from "@mantine/core";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import Roles from "@security/roles";
import { useAuth } from "@hooks/useAuth";
import { modals } from "@mantine/modals";
import { notifications } from "@mantine/notifications";
import { useQueryClient } from "@tanstack/react-query";
import PrivateComponent from "@components/security/PrivateComponent";

export const Route = createFileRoute("/users/")({
  component: Users,
  beforeLoad: ({ context }) => {
    context.auth.isGranted([Roles.ROLE_ADMIN, Roles.USER_GET_COLLECTION], true);
  },
});

function removeEmptyValues(object: any): any {
  // TODO: Common in helpers
  return Object.fromEntries(Object.entries(object).filter(([_, v]) => v));
}

function Users() {
  const { isGranted } = useAuth();
  const queryClient = useQueryClient();
  const [pagination, setPagination] = useState({
    pageIndex: 0,
    pageSize: 30,
  });
  const [columnFilters, setColumnFilters] = useState<MRT_ColumnFiltersState>(
    []
  );
  const { data: articles, isFetching } = $api.useQuery("get", "/api/users", {
    params: {
      query: removeEmptyValues({
        // Maybe we can do that directly in querySerializer of client ?
        page: pagination.pageIndex + 1,
        id: columnFilters.find((f) => f.id === "id")?.value ?? "",
        level: columnFilters.find((f) => f.id === "level")?.value ?? "",
        reason: columnFilters.find((f) => f.id === "reason")?.value ?? "",
      }),
    },
  });

  const columns = useMemo<
    MRT_ColumnDef<components["schemas"]["User.jsonld"]>[]
  >(
    () => [
      {
        accessorKey: "id",
        header: "ID",
      },
      {
        accessorKey: "email",
        header: "Email",
      },
      {
        accessorKey: "roles",
        header: "Roles",
      },
    ],
    []
  );
  const navigate = useNavigate();
  const { mutate: deleteUser } = $api.useMutation("delete", "/api/users/{id}", {
    onSuccess: () => {
      notifications.show({
        title: "User deleted",
        message: "The user has been deleted",
        color: "green",
      });
      queryClient.invalidateQueries({ queryKey: ["get", "/api/users"] });
    },
    onError: (data) => {
      if (data.status === 403) {
        notifications.show({
          title: "Forbidden",
          message: "You are not allowed to delete the user. Only admins can do that",
          color: "red",
          autoClose: false,
          disallowClose: true,
        });
      }
    }
  });

  const table = useMantineReactTable({
    columns,
    data: articles?.["hydra:member"] ?? [],
    state: { isLoading: isFetching, pagination, columnFilters },
    initialState: { density: "xs" },
    onPaginationChange: setPagination,
    renderRowActions: ({ row }) => (
      <Center>
        <Group>
          <ActionIcon color="yellow" onClick={() => navigate({ to: `/users/$id/edit`, params: { id: row.original.id?.toString() ?? "" } })} disabled={!isGranted([Roles.ROLE_ADMIN, Roles.USER_UPDATE], false)}>
            <IconEdit />
          </ActionIcon>
          <ActionIcon
            color="red"
            onClick={() => {
              modals.openConfirmModal({
                title: "Delete user",
                children: (
                  <Text>
                    Are you sure you want to delete the user {" "}
                    {row.original.email}?
                  </Text>
                ),
                onConfirm: () => {
                  deleteUser({ params: { path: { id: row.original.id?.toString() ?? "" } } })
                },
                labels: { confirm: "Delete", cancel: "Cancel" },
              })
            }}
            disabled={!isGranted([Roles.ROLE_ADMIN, Roles.USER_DELETE], false)}
          >
            <IconTrash />
          </ActionIcon>
        </Group>
      </Center>
    ),
    manualPagination: true,
    rowCount: articles?.["hydra:totalItems"] ?? 0,
    manualFiltering: true,
    onColumnFiltersChange: setColumnFilters,
    enableRowActions: true,
  });

  return (
    <Container fluid>
      <PrivateComponent roles={[Roles.ROLE_ADMIN, Roles.USER_CREATE]}>
        <Button onClick={() => navigate({ to: "/users/create" })}>Create user</Button>
      </PrivateComponent>
      <MantineReactTable table={table} />
    </Container>
  );
}
