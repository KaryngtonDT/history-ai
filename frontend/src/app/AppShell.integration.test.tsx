import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { MemoryRouter, Route, Routes } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ActivityLogProvider } from "@/features/activity/ActivityLogProvider";
import { HomeMissionControl } from "@/features/home";
import { ProductShell } from "@/features/product";
import { I18nProvider } from "@/i18n";
import { workItemService } from "@/services/workItem/WorkItemService";

function ShellTestProviders({ children }: { children: ReactNode }) {
	const queryClient = new QueryClient();

	return (
		<QueryClientProvider client={queryClient}>
			<I18nProvider>
				<ActivityLogProvider>
					<MemoryRouter initialEntries={["/"]}>{children}</MemoryRouter>
				</ActivityLogProvider>
			</I18nProvider>
		</QueryClientProvider>
	);
}

describe("App shell with activity log", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("keeps dashboard mounted after HTTP activity logging", async () => {
		const getSummary = vi.spyOn(workItemService, "getSummary");

		render(
			<ShellTestProviders>
				<Routes>
					<Route element={<ProductShell />}>
						<Route path="/" element={<HomeMissionControl />} />
					</Route>
				</Routes>
			</ShellTestProviders>,
		);

		await waitFor(() => {
			expect(getSummary).toHaveBeenCalled();
		});

		await waitFor(() => {
			expect(
				screen.getByText("What do you want to transform?"),
			).toBeInTheDocument();
		});

		expect(screen.getByText("Activity log")).toBeInTheDocument();
		expect(screen.getAllByText("Lumen").length).toBeGreaterThan(0);
	});
});
