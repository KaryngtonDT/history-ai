import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import type { ReactNode } from "react";
import { BrowserRouter } from "react-router";
import { I18nProvider } from "@/i18n";

const queryClient = new QueryClient();

type AppProvidersProps = {
	children: ReactNode;
};

export function AppProviders({ children }: AppProvidersProps) {
	return (
		<QueryClientProvider client={queryClient}>
			<I18nProvider>
				<BrowserRouter>{children}</BrowserRouter>
			</I18nProvider>
		</QueryClientProvider>
	);
}
