import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { usePipelineStageExecutionLock } from "@/features/pipeline/usePipelineStageExecutionLock";
import { useTranslation } from "@/i18n/useTranslation";
import { lipSyncService } from "@/services/lipsync/LipSyncService";
import type { LipSyncProvider, VideoLipSync } from "@/services/lipsync/types";
import type { TranslationLanguage } from "@/services/translation/types";
import { voiceCloneService } from "@/services/voice/VoiceCloneService";
import { LipSyncPreview } from "../LipSyncPreview";
import { LipSyncSettings } from "../LipSyncSettings";
import styles from "./LipSyncPanel.module.css";

export function LipSyncPanel() {
	const { t } = useTranslation();
	const { videoId = "" } = useParams();
	const { executionLocked, refreshPipeline } =
		usePipelineStageExecutionLock("lip_sync");
	const [voiceCloneAvailable, setVoiceCloneAvailable] = useState(false);
	const [lipSyncEntries, setLipSyncEntries] = useState<VideoLipSync[]>([]);
	const [selectedTargets, setSelectedTargets] = useState<TranslationLanguage[]>(
		["french"],
	);
	const [provider, setProvider] = useState<LipSyncProvider>("latentsync");
	const [activeLanguage, setActiveLanguage] = useState<string | null>(null);
	const [compareMode, setCompareMode] = useState(true);
	const [loading, setLoading] = useState(true);
	const [generating, setGenerating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const voiceClones = await voiceCloneService.listVoiceClones(videoId);
		setVoiceCloneAvailable(voiceClones.length > 0);

		const summaries = await lipSyncService.listLipSyncs(videoId);
		const loaded = await Promise.all(
			summaries.map((summary) =>
				lipSyncService.getLipSync(videoId, summary.targetLanguage),
			),
		);

		const available = loaded.filter(
			(entry): entry is VideoLipSync => entry !== null,
		);

		setLipSyncEntries(available);
		setActiveLanguage(
			(current) => current ?? available[0]?.targetLanguage ?? null,
		);
		setLoading(false);
	}, [videoId]);

	useEffect(() => {
		void loadData();
	}, [loadData]);

	const toggleTargetLanguage = (language: TranslationLanguage) => {
		setSelectedTargets((current) =>
			current.includes(language)
				? current.filter((entry) => entry !== language)
				: [...current, language],
		);
	};

	const handleGenerate = async () => {
		setGenerating(true);
		setError(null);

		try {
			await lipSyncService.generateLipSync(videoId, {
				targetLanguages: selectedTargets,
				provider,
			});
			await loadData();
			await refreshPipeline();
		} catch {
			setError(t("pipeline.lipSync.failed"));
		} finally {
			setGenerating(false);
		}
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.lipSync.loading")} />
			</div>
		);
	}

	if (!voiceCloneAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.lipSync.requiredTitle")}
					description={t("pipeline.lipSync.requiredDescription")}
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.lipSync.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.lipSync.videoId")} {videoId}
					</p>
				</div>
			</header>

			<LipSyncSettings
				selectedTargets={selectedTargets}
				provider={provider}
				generating={generating}
				executionLocked={executionLocked}
				error={error}
				onToggleLanguage={toggleTargetLanguage}
				onProviderChange={setProvider}
				onGenerate={handleGenerate}
			/>

			<LipSyncPreview
				entries={lipSyncEntries}
				activeLanguage={activeLanguage}
				compareMode={compareMode}
				onSelectLanguage={setActiveLanguage}
				onCompareModeChange={setCompareMode}
			/>
		</div>
	);
}
