import {
	createContext,
	type ReactNode,
	useCallback,
	useContext,
	useMemo,
	useState,
} from "react";
import { translate } from "./i18n";
import { resolveInitialLocale, setStoredLocale } from "./languageStorage";
import type { Locale, TranslationParams } from "./types";

interface I18nContextValue {
	locale: Locale;
	setLocale: (locale: Locale) => void;
	t: (key: string, params?: TranslationParams) => string;
}

const I18nContext = createContext<I18nContextValue | null>(null);

type I18nProviderProps = {
	children: ReactNode;
	initialLocale?: Locale;
};

export function I18nProvider({ children, initialLocale }: I18nProviderProps) {
	const [locale, setLocaleState] = useState<Locale>(
		initialLocale ?? resolveInitialLocale(),
	);

	const setLocale = useCallback((nextLocale: Locale) => {
		setLocaleState(nextLocale);
		setStoredLocale(nextLocale);
	}, []);

	const t = useCallback(
		(key: string, params?: TranslationParams) => translate(locale, key, params),
		[locale],
	);

	const value = useMemo(
		() => ({
			locale,
			setLocale,
			t,
		}),
		[locale, setLocale, t],
	);

	return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18nContext(): I18nContextValue {
	const context = useContext(I18nContext);

	if (!context) {
		throw new Error("useI18nContext must be used within I18nProvider");
	}

	return context;
}
