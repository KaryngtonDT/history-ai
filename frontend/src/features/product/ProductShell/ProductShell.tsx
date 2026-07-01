import { Outlet } from "react-router";
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
						<div className={styles.topRow}>
							<div>
								<p className={styles.title}>History AI</p>
								<p className={styles.subtitle}>Knowledge Operating System</p>
							</div>
						</div>
						<ProductBreadcrumbs />
					</header>
					<main className={styles.content}>
						<Outlet />
					</main>
				</div>
				<CommandPalette />
			</div>
		</ProductContextProvider>
	);
}
