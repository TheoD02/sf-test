import { createLazyFileRoute, useNavigate } from "@tanstack/react-router";
import $api from "@api/api";
import {
  MantineReactTable,
  MRT_ColumnFiltersState,
  useMantineReactTable,
  type MRT_ColumnDef,
} from "mantine-react-table";
import { useMemo, useState } from "react";
import { components } from "@api/schema";
import { Container } from "@mantine/core";
import { BarChart } from '@mantine/charts';

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
  const { data: articles, isFetching } = $api.useQuery("get", "/api/batteries", {
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
  const { data: batteryPerHour, isFetching: isFetchingBatteryPerHour } = $api.useQuery("get", "/api/batteries/stats/per-hour", {
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
    MRT_ColumnDef<components["schemas"]["Article.jsonld"]>[]
  >(
    () => [
      {
        accessorKey: "id",
        header: "ID",
      },
      {
        accessorKey: "level",
        header: "Level",
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
    state: { isLoading: isFetching, pagination, columnFilters },
    initialState: { density: "xs" },
    onPaginationChange: setPagination,
    manualPagination: true,
    rowCount: articles?.["hydra:totalItems"] ?? 0,
    manualFiltering: true,
    onColumnFiltersChange: setColumnFilters,
    enableRowActions: true,
  });

  console.log((batteryPerHour?.["hydra:member"] ?? []).map((item) => ({ item: new Date(item.hour).toLocaleTimeString(), value: item.levelAtStart })));
  return (
    <Container fluid>
      <BarChart
        h={300}
        data={(batteryPerHour?.["hydra:member"] ?? []).map((item) => ({ item: new Date(item.hour).toLocaleTimeString(), 'Battery change in %': item.levelChange, standalone: true }))}
        dataKey="item"
        type="waterfall"
        series={[{ name: 'Battery change in %', color: 'blue' }]}
      />
      <MantineReactTable table={table} />
    </Container>
  );
}
