import { useI18nContext } from "./I18nProvider";

export function useTranslation() {
	const { locale, setLocale, t } = useI18nContext();

	return {
		locale,
		setLocale,
		t,
	};
}
