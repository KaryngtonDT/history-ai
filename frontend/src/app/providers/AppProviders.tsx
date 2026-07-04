import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import type { ReactNode } from "react";
import { BrowserRouter } from "react-router";
import { ActivityLogProvider } from "@/features/activity/ActivityLogProvider";
import { I18nProvider } from "@/i18n";

const queryClient = new QueryClient();

type AppProvidersProps = {
	children: ReactNode;
};

export function AppProviders({ children }: AppProvidersProps) {
	return (
		<QueryClientProvider client={queryClient}>
			<I18nProvider>
				<ActivityLogProvider>
					<BrowserRouter>{children}</BrowserRouter>
				</ActivityLogProvider>
			</I18nProvider>
		</QueryClientProvider>
	);
}
