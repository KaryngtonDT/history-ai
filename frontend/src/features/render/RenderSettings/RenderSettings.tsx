import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
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
	error,
	onLanguageChange,
	onProviderChange,
	onFormatChange,
	onQualityChange,
	onGenerate,
}: RenderSettingsProps) {
	return (
		<Card className={styles.root}>
			<h3 className={styles.heading}>Final Render</h3>

			<label className={styles.field} htmlFor="render-language">
				<span className={styles.sectionLabel}>Language</span>
				<select
					id="render-language"
					className={styles.select}
					value={selectedLanguage}
					onChange={(event) =>
						onLanguageChange(event.target.value as TranslationLanguage)
					}
				>
					{TARGET_TRANSLATION_LANGUAGES.map((language) => (
						<option key={language} value={language}>
							{formatTranslationLanguageLabel(language)}
						</option>
					))}
				</select>
			</label>

			<p className={styles.sourceStatus}>
				Source:{" "}
				{lipSyncAvailable
					? "✓ Lip-synced preview available"
					: "Lip-sync required before rendering"}
			</p>

			<label className={styles.field} htmlFor="render-provider">
				<span className={styles.sectionLabel}>Provider</span>
				<select
					id="render-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						onProviderChange(event.target.value as VideoRenderProvider)
					}
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
				<span className={styles.sectionLabel}>Output</span>
				<select
					id="render-format"
					className={styles.select}
					value={format}
					onChange={(event) =>
						onFormatChange(event.target.value as VideoRenderFormat)
					}
				>
					{VIDEO_RENDER_FORMATS.map((entry) => (
						<option key={entry} value={entry}>
							{entry.toUpperCase()}
						</option>
					))}
				</select>
			</label>

			<label className={styles.field} htmlFor="render-quality">
				<span className={styles.sectionLabel}>Quality</span>
				<select
					id="render-quality"
					className={styles.select}
					value={quality}
					onChange={(event) =>
						onQualityChange(event.target.value as VideoRenderQuality)
					}
				>
					{VIDEO_RENDER_QUALITIES.map((entry) => (
						<option key={entry} value={entry}>
							{entry.charAt(0).toUpperCase() + entry.slice(1)}
						</option>
					))}
				</select>
			</label>

			<Button
				type="button"
				onClick={onGenerate}
				disabled={generating || !lipSyncAvailable}
			>
				{generating ? "Rendering..." : "Render Final Video"}
			</Button>

			{error ? <p className={styles.error}>{error}</p> : null}
		</Card>
	);
}
