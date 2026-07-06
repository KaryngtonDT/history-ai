import { describe, expect, it } from "vitest";
import { formatApiErrorBody } from "./formatApiErrorBody";

describe("formatApiErrorBody", () => {
	it("formats structured pipeline error payloads", () => {
		expect(
			formatApiErrorBody({
				message: "Transcript missing",
				failureMessage: "Speech process returned no output.",
				failedStage: "speech_to_text",
				videoStatus: "failed",
				lastProcessingDurationSeconds: 42.8,
			}),
		).toBe(
			"Transcript missing | failure: Speech process returned no output. | stage: speech_to_text | videoStatus: failed | duration: 43s",
		);
	});
});
