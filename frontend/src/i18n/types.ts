export const SUPPORTED_LOCALES = ["en", "fr", "de"] as const;

export type Locale = (typeof SUPPORTED_LOCALES)[number];

export const DEFAULT_LOCALE: Locale = "en";

export type TranslationParams = Record<string, string | number>;

export type TranslationLeaf = string;

export type TranslationTree = {
	[key: string]: TranslationLeaf | TranslationTree;
};

export type TranslationKey<T extends TranslationTree> =
	T extends TranslationLeaf
		? never
		: {
				[K in keyof T & string]: T[K] extends TranslationLeaf
					? K
					: T[K] extends TranslationTree
						? `${K}.${TranslationKey<T[K]>}`
						: never;
			}[keyof T & string];
