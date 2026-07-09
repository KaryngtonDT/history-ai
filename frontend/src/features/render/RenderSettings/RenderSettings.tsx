import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n/useTranslation";
import {
	VIDEO_RENDER_FORMATS,
	VIDEO_RENDER_PROVIDERS,
	VIDEO_RENDER_QUALITIES,
	type VideoRenderFormat,
	type VideoRenderProvider,
	type VideoRenderQuality,
} from "@/services/render/types";
import {
	formatTranslationLanguageLabel,
	TARGET_TRANSLATION_LANGUAGES,
	type TranslationLanguage,
} from "@/services/translation/types";
import styles from "./RenderSettings.module.css";

interface RenderSettingsProps {
	selectedLanguage: TranslationLanguage;
	provider: VideoRenderProvider;
	format: VideoRenderFormat;
	quality: VideoRenderQuality;
	lipSyncAvailable: boolean;
	generating: boolean;
	executionLocked?: boolean;
	error: string | null;
	onLanguageChange: (language: TranslationLanguage) => void;
	onProviderChange: (provider: VideoRenderProvider) => void;
	onFormatChange: (format: VideoRenderFormat) => void;
	onQualityChange: (quality: VideoRenderQuality) => void;
	onGenerate: () => void;
}

export function RenderSettings({
	selectedLanguage,
	provider,
	format,
	quality,
	lipSyncAvailable,
	generating,
	executionLocked = false,
	error,
	onLanguageChange,
	onProviderChange,
	onFormatChange,
	onQualityChange,
	onGenerate,
}: RenderSettingsProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.root}>
			<h3 className={styles.heading}>{t("pipeline.render.settingsHeading")}</h3>

			<label className={styles.field} htmlFor="render-language">
				<span className={styles.sectionLabel}>
					{t("pipeline.render.language")}
				</span>
				<select
					id="render-language"
					className={styles.select}
					value={selectedLanguage}
					onChange={(event) =>
						onLanguageChange(event.target.value as TranslationLanguage)
					}
					disabled={executionLocked}
				>
					{TARGET_TRANSLATION_LANGUAGES.map((language) => (
						<option key={language} value={language}>
							{formatTranslationLanguageLabel(language)}
						</option>
					))}
				</select>
			</label>

			<p className={styles.sourceStatus}>
				{t("pipeline.render.sourceLabel")}{" "}
				{lipSyncAvailable
					? t("pipeline.render.sourceReady")
					: t("pipeline.render.sourceMissing")}
			</p>

			<label className={styles.field} htmlFor="render-provider">
				<span className={styles.sectionLabel}>
					{t("pipeline.render.provider")}
				</span>
				<select
					id="render-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						onProviderChange(event.target.value as VideoRenderProvider)
					}
					disabled={executionLocked}
				>
					{VIDEO_RENDER_PROVIDERS.map((entry) => (
						<option
							key={entry.value}
							value={entry.value}
							disabled={!entry.enabled}
						>
							{entry.label}
						</option>
					))}
				</select>
			</label>

			<label className={styles.field} htmlFor="render-format">
				<span className={styles.sectionLabel}>
					{t("pipeline.render.output")}
				</span>
				<select
					id="render-format"
					className={styles.select}
					value={format}
					onChange={(event) =>
						onFormatChange(event.target.value as VideoRenderFormat)
					}
					disabled={executionLocked}
				>
					{VIDEO_RENDER_FORMATS.map((entry) => (
						<option key={entry} value={entry}>
							{entry.toUpperCase()}
						</option>
					))}
				</select>
			</label>

			<label className={styles.field} htmlFor="render-quality">
				<span className={styles.sectionLabel}>
					{t("pipeline.render.quality")}
				</span>
				<select
					id="render-quality"
					className={styles.select}
					value={quality}
					onChange={(event) =>
						onQualityChange(event.target.value as VideoRenderQuality)
					}
					disabled={executionLocked}
				>
					{VIDEO_RENDER_QUALITIES.map((entry) => (
						<option key={entry} value={entry}>
							{entry.charAt(0).toUpperCase() + entry.slice(1)}
						</option>
					))}
				</select>
			</label>

			{executionLocked ? (
				<p className={styles.error}>{t("pipeline.render.executionLocked")}</p>
			) : (
				<Button
					type="button"
					onClick={onGenerate}
					disabled={generating || !lipSyncAvailable}
				>
					{generating
						? t("pipeline.render.rendering")
						: t("pipeline.render.renderCta")}
				</Button>
			)}

			{error ? <p className={styles.error}>{error}</p> : null}
		</Card>
	);
}
