import { AppShell } from "@mantine/core";
import { Outlet } from "@tanstack/react-router";
import { Header } from "./Header";

export default function Shell() {
  return (
    <AppShell
      header={{ height: 60 }}
      padding="md"
    >
      <AppShell.Header>
        <Header />
      </AppShell.Header>

      <AppShell.Main>
        <Outlet />
      </AppShell.Main>
    </AppShell>
  );
}
