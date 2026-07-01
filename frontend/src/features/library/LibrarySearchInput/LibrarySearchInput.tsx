import { useTranslation } from "@/i18n";
import styles from "./LibrarySearchInput.module.css";

interface LibrarySearchInputProps {
	value: string;
	onChange: (value: string) => void;
}

export function LibrarySearchInput({
	value,
	onChange,
}: LibrarySearchInputProps) {
	const { t } = useTranslation();

	return (
		<label className={styles.field}>
			<span className={styles.label}>{t("workspace.library.searchLabel")}</span>
			<input
				type="search"
				className={styles.input}
				value={value}
				onChange={(event) => onChange(event.target.value)}
				placeholder={t("workspace.library.searchPlaceholder")}
				aria-label={t("workspace.library.searchLabel")}
			/>
		</label>
	);
}
