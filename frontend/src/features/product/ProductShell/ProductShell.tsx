import { Outlet } from "react-router";
import { ActivityLogPanel } from "@/features/activity/ActivityLogPanel";
import { useActivityLog } from "@/features/activity/ActivityLogProvider";
import { CommandPalette } from "@/features/guidance/CommandPalette";
import { AppSidebar } from "../AppSidebar";
import { ProductBreadcrumbs } from "../ProductBreadcrumbs";
import { ProductContextProvider } from "../ProductContext";
import styles from "./ProductShell.module.css";

export function ProductShell() {
	const { entries, clear } = useActivityLog();

	return (
		<ProductContextProvider>
			<div className={styles.root}>
				<AppSidebar />
				<div className={styles.main}>
					<header className={styles.top}>
						<ProductBreadcrumbs />
					</header>
					<main className={styles.content}>
						<Outlet />
					</main>
				</div>
				<CommandPalette />
				<ActivityLogPanel entries={entries} onClear={clear} />
			</div>
		</ProductContextProvider>
	);
}
