import { Outlet } from "react-router";
import { CommandPalette } from "@/features/guidance/CommandPalette";
import { useTranslation } from "@/i18n";
import { AppSidebar } from "../AppSidebar";
import { ProductBreadcrumbs } from "../ProductBreadcrumbs";
import { ProductContextProvider } from "../ProductContext";
import styles from "./ProductShell.module.css";

export function ProductShell() {
	const { t } = useTranslation();

	return (
		<ProductContextProvider>
			<div className={styles.root}>
				<AppSidebar />
				<div className={styles.main}>
					<header className={styles.top}>
						<div className={styles.topRow}>
							<div>
								<p className={styles.title}>{t("shell.brand.title")}</p>
								<p className={styles.subtitle}>{t("shell.brand.subtitle")}</p>
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
