import { DEFAULT_LOCALE, type Locale, SUPPORTED_LOCALES } from "./types";

const STORAGE_KEY = "history-ai-locale";

function isLocale(value: string): value is Locale {
	return (SUPPORTED_LOCALES as readonly string[]).includes(value);
}

export function getStoredLocale(): Locale | null {
	if (typeof window === "undefined") {
		return null;
	}

	try {
		const stored = window.localStorage.getItem(STORAGE_KEY);

		if (stored && isLocale(stored)) {
			return stored;
		}
	} catch {
		return null;
	}

	return null;
}

export function setStoredLocale(locale: Locale): void {
	if (typeof window === "undefined") {
		return;
	}

	try {
		window.localStorage.setItem(STORAGE_KEY, locale);
	} catch {
		// Ignore quota or privacy errors.
	}
}

export function detectBrowserLocale(): Locale {
	if (typeof navigator === "undefined") {
		return DEFAULT_LOCALE;
	}

	const languages = navigator.languages?.length
		? navigator.languages
		: [navigator.language];

	for (const language of languages) {
		const normalized = language.toLowerCase();

		if (normalized.startsWith("fr")) {
			return "fr";
		}

		if (normalized.startsWith("de")) {
			return "de";
		}

		if (normalized.startsWith("en")) {
			return "en";
		}
	}

	return DEFAULT_LOCALE;
}

export function resolveInitialLocale(): Locale {
	return getStoredLocale() ?? detectBrowserLocale();
}
