import { describe, expect, it } from "vitest";
import { generateSummaryFromTranscript } from "./summaryGenerator";

describe("generateSummaryFromTranscript", () => {
	it("returns first three meaningful sentences", () => {
		const transcript =
			"The Roman Empire was vast. It lasted many centuries. Its legacy shaped Europe. Modern law still reflects Roman ideas. Archaeology continues to reveal new sites.";

		expect(generateSummaryFromTranscript(transcript)).toBe(
			"The Roman Empire was vast. It lasted many centuries. Its legacy shaped Europe.",
		);
	});

	it("uses full transcript when fewer than three sentences", () => {
		expect(
			generateSummaryFromTranscript(
				"The Roman Empire was a vast civilization.",
			),
		).toBe("The Roman Empire was a vast civilization.");
	});

	it("rejects empty transcript", () => {
		expect(() => generateSummaryFromTranscript("")).toThrow(
			"Transcript is empty; cannot generate summary.",
		);
	});
});
