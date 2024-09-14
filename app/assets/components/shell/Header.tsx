import {
  Group,
  Button,
  UnstyledButton,
  Divider,
  Box,
  Burger,
  Drawer,
  ScrollArea,
  rem,
  Menu,
} from "@mantine/core";
import {useDisclosure} from "@mantine/hooks";
import classes from "./Header.module.css";
import {Link} from "@tanstack/react-router";
import {IconLogout} from "@tabler/icons-react";
import { useAuth } from "@hooks/useAuth";

export function Header() {
  const { user, logout, isLoading } = useAuth();

  const [drawerOpened, {toggle: toggleDrawer, close: closeDrawer}] =
    useDisclosure(false);

  return (
    <Box>
      <header className={classes.header}>
        <Group justify="space-between" h="100%">
          <Box></Box>

          <Group h="100%" gap={0} visibleFrom="sm">
            <UnstyledButton
              component={Link}
              to="/"
              className={classes.link}
              px={12}
            >
              Home
            </UnstyledButton>
            <UnstyledButton
              component={Link}
              to="/users"
              className={classes.link}
              px={12}
            >
              Users
            </UnstyledButton>
            <UnstyledButton
              component={Link}
              to="/about"
              className={classes.link}
              px={12}
            >
              About
            </UnstyledButton>
          </Group>

          <Group visibleFrom="sm">
            {user !== null ? (
              <Menu trigger="hover" openDelay={100} closeDelay={400}>
                <Menu.Target>
                  <Button color="indigo">{user?.email}</Button>
                </Menu.Target>
                <Menu.Dropdown>
                  <Menu.Label>Actions</Menu.Label>
                  <Menu.Item
                    color="red"
                    onClick={() => logout()}
                    leftSection={
                      <IconLogout style={{width: rem(14), height: rem(14)}}/>
                    }
                  >
                    Logout
                  </Menu.Item>
                </Menu.Dropdown>
              </Menu>
            ) : (
              <>
                <Button
                  component={Link}
                  to="/auth/login"
                  variant="default"
                  loading={isLoading}
                >
                  Log in
                </Button>
                <Button
                  component={Link}
                  to="/auth/register"
                  loading={isLoading}
                >
                  Sign up
                </Button>
              </>
            )}
          </Group>

          <Burger
            opened={drawerOpened}
            onClick={toggleDrawer}
            hiddenFrom="sm"
          />
        </Group>
      </header>

      <Drawer
        opened={drawerOpened}
        onClose={closeDrawer}
        size="100%"
        padding="md"
        title="Navigation"
        hiddenFrom="sm"
        zIndex={1000000}
      >
        <ScrollArea h={`calc(100vh - ${rem(80)})`} mx="-md">
          <Divider my="sm"/>

          <UnstyledButton
            component={Link}
            to="/"
            className={classes.link}
            px={12}
          >
            Home
          </UnstyledButton>
          <UnstyledButton
            component={Link}
            to="/articles"
            className={classes.link}
            px={12}
          >
            Articles
          </UnstyledButton>
          <UnstyledButton
            component={Link}
            to="/about"
            className={classes.link}
            px={12}
          >
            About
          </UnstyledButton>

          <Divider my="sm"/>

          <Group justify="center" grow pb="xl" px="md">
            <Button component={Link} to="/auth/login" variant="default">
              Log in
            </Button>
            <Button component={Link} to="/auth/register">
              Sign up
            </Button>
          </Group>
        </ScrollArea>
      </Drawer>
    </Box>
  );
}
