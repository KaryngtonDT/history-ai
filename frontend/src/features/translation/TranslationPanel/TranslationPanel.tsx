import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
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
	const { videoId = "", audioId = "" } = useParams();
	const resourceId = videoId || audioId;
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
		} catch {
			setError("Translation generation failed.");
		} finally {
			setGenerating(false);
		}
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label="Loading translations" />
			</div>
		);
	}

	if (!transcript) {
		return (
			<div className={styles.root}>
				<EmptyState
					title="Transcript required"
					description="Generate a transcript before creating translations."
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
					<h2 className={styles.title}>Translation</h2>
					<p className={styles.meta}>Video ID: {transcript.videoId}</p>
				</div>
				<Badge variant="info">
					Detected: {formatTranslationLanguageLabel(transcript.language)}
				</Badge>
			</header>

			<Card className={styles.controls}>
				<p className={styles.sectionLabel}>Target languages</p>
				<div className={styles.checkboxGroup}>
					{TARGET_TRANSLATION_LANGUAGES.map((language) => (
						<label key={language} className={styles.checkboxLabel}>
							<input
								type="checkbox"
								checked={selectedTargets.includes(language)}
								onChange={() => toggleTargetLanguage(language)}
							/>
							{formatTranslationLanguageLabel(language)}
						</label>
					))}
				</div>

				<label className={styles.field} htmlFor="translation-provider">
					Translation engine
				</label>
				<select
					id="translation-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						setProvider(event.target.value as TranslationProvider)
					}
				>
					{TRANSLATION_PROVIDERS.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>

				<Button
					type="button"
					onClick={handleGenerate}
					disabled={generating || selectedTargets.length === 0}
				>
					{generating ? "Generating..." : "Generate Translation"}
				</Button>

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
									<span className={styles.segmentLabel}>Source</span>
									<p>{segment.sourceText}</p>
								</div>
								<div className={styles.targetColumn}>
									<span className={styles.segmentLabel}>Translation</span>
									<p>{segment.translatedText}</p>
								</div>
							</div>
						))}
					</div>

					<p className={styles.summaryMeta}>
						{activeTranslation.segmentCount} segments ·{" "}
						{formatTranscriptTimestamp(transcript.duration)} source duration
					</p>
				</Card>
			) : (
				<EmptyState
					title="No translations yet"
					description="Select target languages and generate translations."
				/>
			)}
		</div>
	);
}
