import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n/useTranslation";
import { lipSyncService } from "@/services/lipsync/LipSyncService";
import type {
	VideoRender,
	VideoRenderFormat,
	VideoRenderProvider,
	VideoRenderQuality,
} from "@/services/render/types";
import { videoRenderService } from "@/services/render/VideoRenderService";
import type { TranslationLanguage } from "@/services/translation/types";
import { FinalVideoPlayer } from "../FinalVideoPlayer";
import { RenderSettings } from "../RenderSettings";
import styles from "./FinalVideoPanel.module.css";

export function FinalVideoPanel() {
	const { t } = useTranslation();
	const { videoId = "" } = useParams();
	const [lipSyncAvailable, setLipSyncAvailable] = useState(false);
	const [renderEntries, setRenderEntries] = useState<VideoRender[]>([]);
	const [selectedLanguage, setSelectedLanguage] =
		useState<TranslationLanguage>("french");
	const [provider, setProvider] = useState<VideoRenderProvider>("ffmpeg");
	const [format, setFormat] = useState<VideoRenderFormat>("mp4");
	const [quality, setQuality] = useState<VideoRenderQuality>("standard");
	const [activeLanguage, setActiveLanguage] = useState<string | null>(null);
	const [loading, setLoading] = useState(true);
	const [generating, setGenerating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const lipSyncs = await lipSyncService.listLipSyncs(videoId);
		setLipSyncAvailable(lipSyncs.length > 0);

		const summaries = await videoRenderService.listRenders(videoId);
		const loaded = await Promise.all(
			summaries.map((summary) =>
				videoRenderService.getRender(videoId, summary.targetLanguage),
			),
		);

		const available = loaded.filter(
			(entry): entry is VideoRender => entry !== null,
		);

		setRenderEntries(available);
		setActiveLanguage(
			(current) => current ?? available[0]?.targetLanguage ?? null,
		);
		setLoading(false);
	}, [videoId]);

	useEffect(() => {
		void loadData();
	}, [loadData]);

	const handleGenerate = async () => {
		setGenerating(true);
		setError(null);

		try {
			await videoRenderService.generateRender(videoId, {
				targetLanguages: [selectedLanguage],
				provider,
				format,
				quality,
			});
			await loadData();
			setActiveLanguage(selectedLanguage);
		} catch {
			setError(t("pipeline.render.failed"));
		} finally {
			setGenerating(false);
		}
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.render.loading")} />
			</div>
		);
	}

	if (!lipSyncAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.render.requiredTitle")}
					description={t("pipeline.render.requiredDescription")}
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.render.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.render.videoId")} {videoId}
					</p>
				</div>
			</header>

			<RenderSettings
				selectedLanguage={selectedLanguage}
				provider={provider}
				format={format}
				quality={quality}
				lipSyncAvailable={lipSyncAvailable}
				generating={generating}
				error={error}
				onLanguageChange={setSelectedLanguage}
				onProviderChange={setProvider}
				onFormatChange={setFormat}
				onQualityChange={setQuality}
				onGenerate={handleGenerate}
			/>

			<FinalVideoPlayer
				entries={renderEntries}
				activeLanguage={activeLanguage}
				onSelectLanguage={setActiveLanguage}
			/>
		</div>
	);
}
