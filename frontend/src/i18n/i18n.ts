import { de, en, fr, type Messages } from "./locales/en";
import {
	DEFAULT_LOCALE,
	type Locale,
	type TranslationParams,
	type TranslationTree,
} from "./types";

const dictionaries: Record<Locale, Messages> = {
	en,
	fr,
	de,
};

function getNestedValue(
	tree: TranslationTree,
	path: string,
): string | undefined {
	const segments = path.split(".");
	let current: TranslationTree | string | undefined = tree;

	for (const segment of segments) {
		if (current === undefined || typeof current === "string") {
			return undefined;
		}

		current = current[segment];
	}

	return typeof current === "string" ? current : undefined;
}

function interpolate(template: string, params?: TranslationParams): string {
	if (!params) {
		return template;
	}

	return template.replace(/\{\{(\w+)\}\}/g, (_, key: string) => {
		const value = params[key];
		return value === undefined ? `{{${key}}}` : String(value);
	});
}

export function translate(
	locale: Locale,
	key: string,
	params?: TranslationParams,
): string {
	const primary = getNestedValue(dictionaries[locale], key);

	if (primary !== undefined) {
		return interpolate(primary, params);
	}

	if (locale !== DEFAULT_LOCALE) {
		const fallback = getNestedValue(dictionaries[DEFAULT_LOCALE], key);

		if (fallback !== undefined) {
			return interpolate(fallback, params);
		}
	}

	return key;
}

export function getDictionary(locale: Locale): Messages {
	return dictionaries[locale];
}
