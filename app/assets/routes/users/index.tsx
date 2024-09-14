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
import { ActionIcon, Box, Center, Container, Group, Menu } from "@mantine/core";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import Roles from "@security/roles";

export const Route = createFileRoute("/users/")({
  component: Users,
  beforeLoad: ({ context }) => {
    context.auth.isGranted([Roles.ROLE_ADMIN], true);
  },
});

function removeEmptyValues(object: any): any {
  // TODO: Common in helpers
  return Object.fromEntries(Object.entries(object).filter(([_, v]) => v));
}

function Users() {
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
  const { mutate: deleteUser } = $api.useMutation("delete", "/api/users/{id}");

  const table = useMantineReactTable({
    columns,
    data: articles?.["hydra:member"] ?? [],
    state: { isLoading: isFetching, pagination, columnFilters },
    initialState: { density: "xs" },
    onPaginationChange: setPagination,
    renderRowActions: ({ row }) => (
      <Center>
        <Group>
          <ActionIcon onClick={() => navigate({ to: `/users/$id/edit`, params:  {id: row.original.id?.toString() ?? ""} })}>
            <IconEdit />
          </ActionIcon>
          <ActionIcon onClick={() => confirm("Are you sure ?") && deleteUser({ params: { path: { id: row.original.id?.toString() ?? "" } } })}>
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
      <MantineReactTable table={table} />
    </Container>
  );
}
