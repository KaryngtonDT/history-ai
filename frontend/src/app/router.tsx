import { Route, Routes } from "react-router";
import { AppLayout } from "@/app/AppLayout";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { SettingsPage } from "@/pages/Settings/SettingsPage";

export function AppRouter() {
	return (
		<Routes>
			<Route element={<AppLayout />}>
				<Route path="/" element={<DashboardPage />} />
				<Route path="/import" element={<ImportPage />} />
				<Route path="/library" element={<LibraryPage />} />
				<Route path="/settings" element={<SettingsPage />} />
			</Route>
		</Routes>
	);
}
