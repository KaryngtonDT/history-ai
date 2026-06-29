import {
	formatTranslationLanguageLabel,
	type TranslationLanguage,
} from "@/services/translation/types";
import styles from "./TranslationLanguageTabs.module.css";

interface TranslationLanguageTabsProps {
	languages: TranslationLanguage[];
	activeLanguage: TranslationLanguage | null;
	onSelect: (language: TranslationLanguage) => void;
}

export function TranslationLanguageTabs({
	languages,
	activeLanguage,
	onSelect,
}: TranslationLanguageTabsProps) {
	if (languages.length === 0) {
		return null;
	}

	return (
		<div className={styles.tabs}>
			{languages.map((language) => {
				const isActive = language === activeLanguage;

				return (
					<button
						key={language}
						type="button"
						className={isActive ? styles.tabActive : styles.tab}
						onClick={() => onSelect(language)}
					>
						{formatTranslationLanguageLabel(language)}
					</button>
				);
			})}
		</div>
	);
}
