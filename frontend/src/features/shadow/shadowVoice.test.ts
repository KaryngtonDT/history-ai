import { describe, expect, it } from "vitest";
import {
	effectiveSpeechLanguage,
	resolveRecognitionLanguage,
	resolveSpeechLanguageFromAnswer,
	selectedLanguageFromPreference,
	toVoicePreferencePayload,
} from "./shadowVoice";

describe("shadowVoice", () => {
	it("maps auto preference to target language mode", () => {
		expect(toVoicePreferencePayload("auto")).toEqual({
			mode: "same_as_target_language",
		});
	});

	it("maps manual French preference", () => {
		expect(toVoicePreferencePayload("fr")).toEqual({
			mode: "manual",
			manualLanguage: "fr",
		});
	});

	it("derives selected language from manual preference", () => {
		expect(
			selectedLanguageFromPreference({
				mode: "manual",
				manualLanguage: "de",
			}),
		).toBe("de");
	});

	it("resolves effective speech language from target language", () => {
		expect(effectiveSpeechLanguage("auto", "fr")).toBe("fr");
		expect(effectiveSpeechLanguage("auto", "deutsch")).toBe("de");
		expect(effectiveSpeechLanguage("en", "fr")).toBe("en");
	});

	it("resolves recognition language from selected language", () => {
		expect(resolveRecognitionLanguage("fr", "en", "en")).toBe("fr-FR");
		expect(resolveRecognitionLanguage("auto", "de", "en")).toBe("de-DE");
	});

	it("falls back to English for unknown answer speech language", () => {
		expect(resolveSpeechLanguageFromAnswer(undefined)).toBe("en");
		expect(resolveSpeechLanguageFromAnswer("fr")).toBe("fr");
	});
});
