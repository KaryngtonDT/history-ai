import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ShadowVoiceSettings } from "@/features/shadow/ShadowVoiceSettings";
import { renderWithProviders } from "@/test/render";

describe("ShadowVoiceSettings", () => {
	it("renders speaking language selector", () => {
		renderWithProviders(
			<ShadowVoiceSettings
				selectedLanguage="auto"
				onChange={() => undefined}
			/>,
		);

		expect(
			screen.getByLabelText("Shadow speaking language"),
		).toBeInTheDocument();
		expect(
			screen.getByRole("option", { name: "Auto (match target language)" }),
		).toBeInTheDocument();
	});

	it("updates selected language", async () => {
		const onChange = vi.fn();
		const user = userEvent.setup();

		renderWithProviders(
			<ShadowVoiceSettings selectedLanguage="auto" onChange={onChange} />,
		);

		await user.selectOptions(
			screen.getByLabelText("Shadow speaking language"),
			"fr",
		);

		expect(onChange).toHaveBeenCalledWith("fr");
	});

	it("renders in French", () => {
		renderWithProviders(
			<ShadowVoiceSettings
				selectedLanguage="auto"
				onChange={() => undefined}
			/>,
			{ locale: "fr" },
		);

		expect(screen.getByText("Voix Shadow")).toBeInTheDocument();
	});
});
