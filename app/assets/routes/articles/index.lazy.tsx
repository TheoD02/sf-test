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
import { Box, Button, Container, LoadingOverlay, Select } from "@mantine/core";
import { BarChart } from '@mantine/charts';
import { useForm } from "@mantine/form";
import { DatePicker, DatePickerInput, DateTimePicker } from "@mantine/dates";
import { format, formatDistance } from "date-fns";

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
  const chartFilters = useForm({
    initialValues: {
      range: "hour",
      dateRange: [new Date(), null],
    },
  });

  console.log(chartFilters.values);
  const { data: batteryPerHour, isFetching: isFetchingBatteryPerHour } = $api.useQuery("get", "/api/batteries/stats/per-hour", {
    params: {
      query: removeEmptyValues({
        // Maybe we can do that directly in querySerializer of client ?
        from: chartFilters.values.dateRange[0]?.toISOString(),
        to: chartFilters.values.dateRange[1]?.toISOString(),
        range: chartFilters.values.range,
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

  return (
    <Container fluid>
      <Box pos="relative">
        <LoadingOverlay visible={isFetchingBatteryPerHour} zIndex={1000} />
        <Select
          data={[
            { value: "hour", label: "Per hour" },
            { value: "tenMinute", label: "Per 10 minutes" },
          ]}
          label="Range"
          placeholder="Pick range"
          {...chartFilters.getInputProps("range")}
        />
        <DatePickerInput
          type="range"
          label="Pick dates range"
          placeholder="Pick dates range"
          allowSingleDateInRange
          highlightToday
          clearable
          {...chartFilters.getInputProps("dateRange")}
        />
        <BarChart
          h={300}
          data={(batteryPerHour?.["hydra:member"] ?? []).map((item) => ({
            item: formatDistance(new Date(item.hour), new Date(), { addSuffix: true }),
            'Battery change in %': item.levelChange,
            'Battery level at start in %': item.levelAtStart,
            'Battery level at end in %': item.levelAtEnd,
            standalone: true,
          }))}
          dataKey="item"
          type="waterfall"
          series={[
            { name: 'Battery change in %', color: 'blue', },
            { name: 'Battery level at start in %', color: 'green' },
            { name: 'Battery level at end in %', color: 'orange' },
          ]}
          withLegend
          referenceLines={[
            { y: 10, color: 'red', label: 'Critical battery level' },
            { y: 20, color: 'orange', label: 'Warning battery level' },
            { y: 50, color: 'cyan', label: 'Good battery level' },
            { y: 80, color: 'blue', label: 'Safe battery level' },
            { y: 100, color: 'green', label: 'Full battery level' },
          ]}
          withBarValueLabel
        />
      </Box>
      <MantineReactTable table={table} />
    </Container>
  );
}
