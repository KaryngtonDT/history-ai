import { type Locale, SUPPORTED_LOCALES, useTranslation } from "@/i18n";
import styles from "./LanguageSettings.module.css";

const LOCALE_LABEL_KEYS: Record<Locale, string> = {
	en: "language.en",
	fr: "language.fr",
	de: "language.de",
};

export function LanguageSettings() {
	const { locale, setLocale, t } = useTranslation();

	return (
		<section
			className={styles.root}
			aria-labelledby="language-settings-heading"
		>
			<h2 id="language-settings-heading" className={styles.title}>
				{t("settings.language.title")}
			</h2>
			<p className={styles.description}>{t("settings.language.description")}</p>
			<div
				className={styles.options}
				role="radiogroup"
				aria-label={t("language.label")}
			>
				{SUPPORTED_LOCALES.map((option) => (
					<label key={option} className={styles.option}>
						<input
							type="radio"
							name="ui-locale"
							value={option}
							checked={locale === option}
							onChange={() => setLocale(option)}
						/>
						<span>{t(LOCALE_LABEL_KEYS[option])}</span>
					</label>
				))}
			</div>
		</section>
	);
}
