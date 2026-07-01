import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import { LanguageSettings } from "@/features/settings/LanguageSettings";
import { I18nProvider } from "@/i18n";

describe("LanguageSettings", () => {
	it("renders language options in French", async () => {
		const user = userEvent.setup();

		render(
			<I18nProvider initialLocale="en">
				<LanguageSettings />
			</I18nProvider>,
		);

		await user.click(screen.getByRole("radio", { name: "Français" }));

		expect(screen.getByText("Langue de l'interface")).toBeInTheDocument();
	});
});
