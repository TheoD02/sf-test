import { Title, Text, Button, Container, Group, Center } from '@mantine/core';
import classes from './Error.module.css';
import { useNavigate } from '@tanstack/react-router';

type Props = {
  code: number;
  label: string;
  description: string;
};

export default function Error({ code, label, description }: Props) {
  const navigate = useNavigate();

  return (
    <Container className={classes.root}>
      <div className={classes.label}>{code}</div>
      <Title className={classes.title}>{label}</Title>
      <Center>
        <Text c="dimmed" size="lg" ta="center" className={classes.description}>
          {description}
        </Text>
      </Center>
      <Group justify="center">
        <Button color="gray" size="md" onClick={() => window.history.back()}>
          Take me back to previous page
        </Button>
        <Button variant="primary" size="md" onClick={() => navigate({ to: '/' })}>
          Go to homepage
        </Button>
      </Group>
    </Container>
  );
}
