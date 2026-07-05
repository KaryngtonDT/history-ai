import { Outlet } from "react-router";
import { PageActivityLog } from "@/features/activity/PageActivityLog";
import { CommandPalette } from "@/features/guidance/CommandPalette";
import { AppSidebar } from "../AppSidebar";
import { ProductBreadcrumbs } from "../ProductBreadcrumbs";
import { ProductContextProvider } from "../ProductContext";
import styles from "./ProductShell.module.css";

export function ProductShell() {
	return (
		<ProductContextProvider>
			<div className={styles.root}>
				<AppSidebar />
				<div className={styles.main}>
					<header className={styles.top}>
						<ProductBreadcrumbs />
					</header>
					<main className={styles.content}>
						<PageActivityLog />
						<Outlet />
					</main>
				</div>
				<CommandPalette />
			</div>
		</ProductContextProvider>
	);
}
