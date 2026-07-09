import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { usePipelineStageExecutionLock } from "@/features/pipeline/usePipelineStageExecutionLock";
import { useTranslation } from "@/i18n/useTranslation";
import { transcriptService } from "@/services/transcript/TranscriptService";
import type { VideoTranscript } from "@/services/transcript/types";
import { formatTranscriptTimestamp } from "@/services/transcript/types";
import { translationService } from "@/services/translation/TranslationService";
import {
	formatTranslationLanguageLabel,
	formatTranslationProviderLabel,
	TARGET_TRANSLATION_LANGUAGES,
	TRANSLATION_PROVIDERS,
	type TranslationLanguage,
	type TranslationProvider,
	type VideoTranslation,
} from "@/services/translation/types";
import { TranslationLanguageTabs } from "../TranslationLanguageTabs";
import styles from "./TranslationPanel.module.css";

export function TranslationPanel() {
	const { t } = useTranslation();
	const { videoId = "", audioId = "" } = useParams();
	const resourceId = videoId || audioId;
	const { executionLocked, refreshPipeline } =
		usePipelineStageExecutionLock("translation");
	const [transcript, setTranscript] = useState<VideoTranscript | null>(null);
	const [translations, setTranslations] = useState<VideoTranslation[]>([]);
	const [selectedTargets, setSelectedTargets] = useState<TranslationLanguage[]>(
		["french", "german"],
	);
	const [provider, setProvider] = useState<TranslationProvider>("qwen");
	const [activeLanguage, setActiveLanguage] =
		useState<TranslationLanguage | null>(null);
	const [loading, setLoading] = useState(true);
	const [generating, setGenerating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const [transcriptResult, summaries] = await Promise.all([
			transcriptService.getTranscript(resourceId),
			translationService.listTranslations(resourceId),
		]);

		setTranscript(transcriptResult);

		const loadedTranslations = await Promise.all(
			summaries.map((summary) =>
				translationService.getTranslation(resourceId, summary.targetLanguage),
			),
		);

		const available = loadedTranslations.filter(
			(entry): entry is VideoTranslation => entry !== null,
		);

		setTranslations(available);
		setActiveLanguage(
			(current) => current ?? available[0]?.targetLanguage ?? null,
		);
		setLoading(false);
	}, [resourceId]);

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
			await translationService.generateTranslations(resourceId, {
				targetLanguages: selectedTargets,
				provider,
			});
			await loadData();
			await refreshPipeline();
		} catch {
			setError(t("pipeline.translation.failed"));
		} finally {
			setGenerating(false);
		}
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.translation.loading")} />
			</div>
		);
	}

	if (!transcript) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.translation.requiredTitle")}
					description={t("pipeline.translation.requiredDescription")}
				/>
			</div>
		);
	}

	const activeTranslation =
		translations.find((entry) => entry.targetLanguage === activeLanguage) ??
		null;

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.translation.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.translation.videoId")} {transcript.videoId}
					</p>
				</div>
				<Badge variant="info">
					{t("pipeline.translation.detected")}{" "}
					{formatTranslationLanguageLabel(transcript.language)}
				</Badge>
			</header>

			<Card className={styles.controls}>
				<p className={styles.sectionLabel}>
					{t("pipeline.translation.targetLanguages")}
				</p>
				<div className={styles.checkboxGroup}>
					{TARGET_TRANSLATION_LANGUAGES.map((language) => (
						<label key={language} className={styles.checkboxLabel}>
							<input
								type="checkbox"
								checked={selectedTargets.includes(language)}
								onChange={() => toggleTargetLanguage(language)}
								disabled={executionLocked}
							/>
							{formatTranslationLanguageLabel(language)}
						</label>
					))}
				</div>

				<label className={styles.field} htmlFor="translation-provider">
					{t("pipeline.translation.engine")}
				</label>
				<select
					id="translation-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						setProvider(event.target.value as TranslationProvider)
					}
					disabled={executionLocked}
				>
					{TRANSLATION_PROVIDERS.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>

				{executionLocked ? (
					<p className={styles.meta}>
						{t("pipeline.translation.executionLocked")}
					</p>
				) : (
					<Button
						type="button"
						onClick={handleGenerate}
						disabled={generating || selectedTargets.length === 0}
					>
						{generating
							? t("pipeline.translation.generating")
							: t("pipeline.translation.generateCta")}
					</Button>
				)}

				{error ? <p className={styles.error}>{error}</p> : null}
			</Card>

			<TranslationLanguageTabs
				languages={translations.map((entry) => entry.targetLanguage)}
				activeLanguage={activeLanguage}
				onSelect={setActiveLanguage}
			/>

			{activeTranslation ? (
				<Card className={styles.comparison}>
					<div className={styles.comparisonHeader}>
						<p className={styles.sectionLabel}>
							{formatTranslationLanguageLabel(activeTranslation.targetLanguage)}
						</p>
						<Badge variant="neutral">
							{formatTranslationProviderLabel(activeTranslation.provider)}
						</Badge>
					</div>

					<div className={styles.segmentList}>
						{activeTranslation.segments.map((segment) => (
							<div key={segment.index} className={styles.segmentRow}>
								<div className={styles.sourceColumn}>
									<span className={styles.segmentLabel}>
										{t("pipeline.translation.sourceLabel")}
									</span>
									<p>{segment.sourceText}</p>
								</div>
								<div className={styles.targetColumn}>
									<span className={styles.segmentLabel}>
										{t("pipeline.translation.translationLabel")}
									</span>
									<p>{segment.translatedText}</p>
								</div>
							</div>
						))}
					</div>

					<p className={styles.summaryMeta}>
						{t("pipeline.translation.sourceDuration", {
							count: activeTranslation.segmentCount,
							duration: formatTranscriptTimestamp(transcript.duration),
						})}
					</p>
				</Card>
			) : (
				<EmptyState
					title={t("pipeline.translation.emptyTitle")}
					description={t("pipeline.translation.emptyDescription")}
				/>
			)}
		</div>
	);
}
