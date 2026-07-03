import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { QuickLauncher } from "./quickAssist/QuickLauncher";

createRoot(document.getElementById("root")!).render(
	<StrictMode>
		<QuickLauncher />
	</StrictMode>,
);
