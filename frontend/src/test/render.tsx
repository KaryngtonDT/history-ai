import { type RenderOptions, render } from "@testing-library/react";
import type { ReactElement, ReactNode } from "react";
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

	function TestProviders({ children }: { children: ReactNode }) {
		const content = Wrapper ? <Wrapper>{children}</Wrapper> : children;

		return (
			<I18nProvider initialLocale={locale ?? "en"}>{content}</I18nProvider>
		);
	}

	return render(ui, { wrapper: TestProviders, ...renderOptions });
}
