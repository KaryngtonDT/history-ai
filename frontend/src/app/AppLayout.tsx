import { Outlet } from "react-router";
import { PageContainer } from "@/components/ui/PageContainer";
import { Sidebar } from "@/components/ui/Sidebar";
import { Topbar } from "@/components/ui/Topbar";
import styles from "./AppLayout.module.css";

export function AppLayout() {
	return (
		<div className={styles.root}>
			<Sidebar />
			<div className={styles.main}>
				<Topbar />
				<PageContainer>
					<Outlet />
				</PageContainer>
			</div>
		</div>
	);
}
