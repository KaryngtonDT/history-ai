import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n/useTranslation";
import {
	LIP_SYNC_PROVIDERS,
	type LipSyncProvider,
} from "@/services/lipsync/types";
import {
	formatTranslationLanguageLabel,
	TARGET_TRANSLATION_LANGUAGES,
	type TranslationLanguage,
} from "@/services/translation/types";
import styles from "./LipSyncSettings.module.css";

interface LipSyncSettingsProps {
	selectedTargets: TranslationLanguage[];
	provider: LipSyncProvider;
	generating: boolean;
	executionLocked?: boolean;
	error: string | null;
	onToggleLanguage: (language: TranslationLanguage) => void;
	onProviderChange: (provider: LipSyncProvider) => void;
	onGenerate: () => void;
}

export function LipSyncSettings({
	selectedTargets,
	provider,
	generating,
	executionLocked = false,
	error,
	onToggleLanguage,
	onProviderChange,
	onGenerate,
}: LipSyncSettingsProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.root}>
			<p className={styles.sectionLabel}>{t("pipeline.lipSync.provider")}</p>
			<select
				id="lip-sync-provider"
				className={styles.select}
				value={provider}
				onChange={(event) =>
					onProviderChange(event.target.value as LipSyncProvider)
				}
				disabled={executionLocked}
			>
				{LIP_SYNC_PROVIDERS.map((entry) => (
					<option
						key={entry.value}
						value={entry.value}
						disabled={!entry.enabled}
					>
						{entry.label}
						{entry.enabled ? "" : " (disabled)"}
					</option>
				))}
			</select>

			<p className={styles.sectionLabel}>
				{t("pipeline.lipSync.clonedAudioLanguages")}
			</p>
			<div className={styles.checkboxGroup}>
				{TARGET_TRANSLATION_LANGUAGES.map((language) => (
					<label key={language} className={styles.checkboxLabel}>
						<input
							type="checkbox"
							checked={selectedTargets.includes(language)}
							onChange={() => onToggleLanguage(language)}
							disabled={executionLocked}
						/>
						{formatTranslationLanguageLabel(language)}
					</label>
				))}
			</div>

			{executionLocked ? (
				<p className={styles.error}>{t("pipeline.lipSync.executionLocked")}</p>
			) : (
				<Button
					type="button"
					onClick={onGenerate}
					disabled={generating || selectedTargets.length === 0}
				>
					{generating
						? t("pipeline.lipSync.generating")
						: t("pipeline.lipSync.generateCta")}
				</Button>
			)}

			{error ? <p className={styles.error}>{error}</p> : null}
		</Card>
	);
}
