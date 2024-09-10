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
import {queryClient} from '../../app';

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
  const {data: articles, isFetching} = $api.useQuery("get", "/api/batteries", {
    params: {
      query: removeEmptyValues({
        // Maybe we can do that directly in querySerializer of client ?
        page: pagination.pageIndex + 1,
        id: columnFilters.find((f) => f.id === "id")?.value ?? "",
        reason: columnFilters.find((f) => f.id === "reason")?.value ?? "",
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
        accessorKey: "reason",
        header: "Reason",
      },
    ],
    []
  );
  const navigate = useNavigate();

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
  });

  return (
    <Container fluid>
      <MantineReactTable table={table}/>
    </Container>
  );
}
