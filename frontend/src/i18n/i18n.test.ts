import { afterEach, describe, expect, it } from "vitest";
import { translate } from "./i18n";
import {
	detectBrowserLocale,
	getStoredLocale,
	setStoredLocale,
} from "./languageStorage";
import { DEFAULT_LOCALE } from "./types";

describe("i18n", () => {
	afterEach(() => {
		window.localStorage.clear();
	});

	it("uses English as default language", () => {
		expect(translate(DEFAULT_LOCALE, "common.save")).toBe("Save");
	});

	it("translates French strings", () => {
		expect(translate("fr", "common.save")).toBe("Enregistrer");
	});

	it("translates German strings", () => {
		expect(translate("de", "common.save")).toBe("Speichern");
	});

	it("falls back to English when key is missing in locale", () => {
		expect(translate("fr", "missing.key.path")).toBe("missing.key.path");
	});

	it("persists selected language in localStorage", () => {
		setStoredLocale("de");
		expect(getStoredLocale()).toBe("de");
	});

	it("detects browser language when supported", () => {
		const originalLanguage = navigator.language;
		const originalLanguages = navigator.languages;

		Object.defineProperty(navigator, "language", {
			configurable: true,
			value: "fr-FR",
		});
		Object.defineProperty(navigator, "languages", {
			configurable: true,
			value: ["fr-FR"],
		});

		expect(detectBrowserLocale()).toBe("fr");

		Object.defineProperty(navigator, "language", {
			configurable: true,
			value: originalLanguage,
		});
		Object.defineProperty(navigator, "languages", {
			configurable: true,
			value: originalLanguages,
		});
	});
});
