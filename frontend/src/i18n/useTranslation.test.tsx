import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import { I18nProvider } from "./I18nProvider";
import { useTranslation } from "./useTranslation";

function LanguageSwitcherProbe() {
	const { locale, setLocale, t } = useTranslation();

	return (
		<div>
			<p data-testid="locale">{locale}</p>
			<p data-testid="label">{t("common.save")}</p>
			<button type="button" onClick={() => setLocale("fr")}>
				Switch to French
			</button>
		</div>
	);
}

describe("useTranslation", () => {
	it("switches language and updates labels", async () => {
		const user = userEvent.setup();

		render(
			<I18nProvider initialLocale="en">
				<LanguageSwitcherProbe />
			</I18nProvider>,
		);

		expect(screen.getByTestId("locale")).toHaveTextContent("en");
		expect(screen.getByTestId("label")).toHaveTextContent("Save");

		await user.click(screen.getByRole("button", { name: "Switch to French" }));

		expect(screen.getByTestId("locale")).toHaveTextContent("fr");
		expect(screen.getByTestId("label")).toHaveTextContent("Enregistrer");
	});
});
