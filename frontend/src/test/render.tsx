import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { type RenderOptions, render } from "@testing-library/react";
import type { ReactElement, ReactNode } from "react";
import { ActivityLogProvider } from "@/features/activity/ActivityLogProvider";
import { I18nProvider } from "@/i18n";

type ProviderOptions = {
	locale?: "en" | "fr" | "de";
	wrapper?: ({ children }: { children: ReactNode }) => ReactElement;
};

export function renderWithProviders(
	ui: ReactElement,
	options?: RenderOptions & ProviderOptions,
) {
	const { locale, wrapper: Wrapper, ...renderOptions } = options ?? {};
	const queryClient = new QueryClient();

	function TestProviders({ children }: { children: ReactNode }) {
		const content = Wrapper ? <Wrapper>{children}</Wrapper> : children;

		return (
			<QueryClientProvider client={queryClient}>
				<I18nProvider initialLocale={locale ?? "en"}>
					<ActivityLogProvider>{content}</ActivityLogProvider>
				</I18nProvider>
			</QueryClientProvider>
		);
	}

	return render(ui, { wrapper: TestProviders, ...renderOptions });
}
