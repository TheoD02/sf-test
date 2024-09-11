import {createRoot} from "react-dom/client";
import App from "./app.tsx";
import "@mantine/core/styles.css";
import '@mantine/dates/styles.css'; //if using mantine date picker features
//import 'mantine-react-table/styles.css'; //import MRT styles
import '@mantine/notifications/styles.css';
import '@mantine/charts/styles.css';
// import '@mantine/dropzone/styles.css';
// import '@mantine/code-highlight/styles.css';

const rootContainer = document.getElementById("root");

if (!rootContainer) {
  throw new Error("Root container not found");
}

const root = createRoot(rootContainer);
root.render(<App/>);
