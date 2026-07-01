import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { renderWithProviders } from "@/test/render";
import { FeatureAcademy } from "../FeatureAcademy";
import { FEATURE_HELP } from "./features";

const EXPECTED_FEATURES = [
	"video-upload",
	"transcript",
	"translation",
	"audio",
	"voice-clone",
	"lip-sync",
	"final-render",
	"quality",
	"pipeline",
	"automatic-mode",
	"ai-engines",
	"optimization",
	"scheduler",
	"history",
	"reprocess",
	"reviews",
	"preferences",
	"collaboration",
	"analytics",
	"workspace",
] as const;

describe("FEATURE_HELP", () => {
	it("includes help content for every product feature", () => {
		for (const id of EXPECTED_FEATURES) {
			const help = FEATURE_HELP[id];
			expect(help.id).toBe(id);
			expect(help.title.length).toBeGreaterThan(0);
			expect(help.short.length).toBeGreaterThan(0);
			expect(help.details.length).toBeGreaterThan(0);
			expect(help.bestPractice.length).toBeGreaterThan(0);
			expect(help.nextStep.length).toBeGreaterThan(0);
		}
	});
});

describe("FeatureAcademy", () => {
	it("renders voice clone help sections", () => {
		renderWithProviders(<FeatureAcademy featureId="voice-clone" />);

		expect(
			screen.getByRole("heading", { name: "Voice Clone" }),
		).toBeInTheDocument();
		expect(screen.getByText("What is it?")).toBeInTheDocument();
		expect(screen.getByText("Best practice")).toBeInTheDocument();
		expect(screen.getByText("Next step")).toBeInTheDocument();
	});
});
