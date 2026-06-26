import { Outlet } from "react-router";
import { PageContainer } from "@/components/ui/PageContainer";
import { Sidebar } from "@/components/ui/Sidebar";
import { Topbar } from "@/components/ui/Topbar";

export function AppLayout() {
	return (
		<div className="flex min-h-screen flex-col bg-[var(--color-bg-base)] lg:flex-row">
			<Sidebar />
			<div className="flex min-h-screen flex-1 flex-col">
				<Topbar />
				<PageContainer>
					<Outlet />
				</PageContainer>
			</div>
		</div>
	);
}
