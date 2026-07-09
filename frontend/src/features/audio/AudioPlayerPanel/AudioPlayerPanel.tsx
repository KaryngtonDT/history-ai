import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { API_BASE_URL } from "@/config/api";
import { usePipelineStageExecutionLock } from "@/features/pipeline/usePipelineStageExecutionLock";
import { useTranslation } from "@/i18n/useTranslation";
import { audioService } from "@/services/audio/AudioService";
import {
	AVAILABLE_VOICES,
	formatAudioDuration,
	formatTextToSpeechProviderLabel,
	resolveAudioStreamUrl,
	type TextToSpeechProvider,
	TTS_PROVIDERS,
	type VideoAudio,
} from "@/services/audio/types";
import { translationService } from "@/services/translation/TranslationService";
import {
	formatTranslationLanguageLabel,
	TARGET_TRANSLATION_LANGUAGES,
	type TranslationLanguage,
} from "@/services/translation/types";
import { VoiceSelector } from "../VoiceSelector";
import styles from "./AudioPlayerPanel.module.css";

export function AudioPlayerPanel() {
	const { t } = useTranslation();
	const { videoId = "" } = useParams();
	const { executionLocked, refreshPipeline } =
		usePipelineStageExecutionLock("text_to_speech");
	const audioRef = useRef<HTMLAudioElement | null>(null);
	const [translationsAvailable, setTranslationsAvailable] = useState(false);
	const [audioEntries, setAudioEntries] = useState<VideoAudio[]>([]);
	const [selectedTargets, setSelectedTargets] = useState<TranslationLanguage[]>(
		["french"],
	);
	const [provider, setProvider] = useState<TextToSpeechProvider>("f5_tts");
	const [voiceId, setVoiceId] = useState(AVAILABLE_VOICES[0]?.voiceId ?? "");
	const [activeLanguage, setActiveLanguage] =
		useState<TranslationLanguage | null>(null);
	const [loading, setLoading] = useState(true);
	const [generating, setGenerating] = useState(false);
	const [isPlaying, setIsPlaying] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const summaries = await translationService.listTranslations(videoId);
		setTranslationsAvailable(summaries.length > 0);

		const audioSummaries = await audioService.listAudio(videoId);
		const loadedAudio = await Promise.all(
			audioSummaries.map((summary) =>
				audioService.getAudio(videoId, summary.targetLanguage),
			),
		);

		const available = loadedAudio.filter(
			(entry): entry is VideoAudio => entry !== null,
		);

		setAudioEntries(available);
		setActiveLanguage(
			(current) => current ?? available[0]?.targetLanguage ?? null,
		);
		setLoading(false);
	}, [videoId]);

	useEffect(() => {
		void loadData();
	}, [loadData]);

	const activeAudio =
		audioEntries.find((entry) => entry.targetLanguage === activeLanguage) ??
		null;

	const streamUrl = useMemo(() => {
		if (!activeAudio) {
			return null;
		}

		return resolveAudioStreamUrl(activeAudio.downloadUrl, API_BASE_URL);
	}, [activeAudio]);

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
			await audioService.generateAudio(videoId, {
				targetLanguages: selectedTargets,
				provider,
				voiceId,
			});
			await loadData();
			await refreshPipeline();
		} catch {
			setError(t("pipeline.audioPanel.failed"));
		} finally {
			setGenerating(false);
		}
	};

	const togglePlayback = async () => {
		const element = audioRef.current;

		if (!element) {
			return;
		}

		if (element.paused) {
			await element.play();
			setIsPlaying(true);
			return;
		}

		element.pause();
		setIsPlaying(false);
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.audioPanel.loading")} />
			</div>
		);
	}

	if (!translationsAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.audioPanel.requiredTitle")}
					description={t("pipeline.audioPanel.requiredDescription")}
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.audioPanel.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.audioPanel.videoId")} {videoId}
					</p>
				</div>
			</header>

			<Card className={styles.controls}>
				<p className={styles.sectionLabel}>
					{t("pipeline.audioPanel.translationLanguages")}
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

				<label className={styles.field} htmlFor="audio-provider">
					{t("pipeline.audioPanel.ttsEngine")}
				</label>
				<select
					id="audio-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						setProvider(event.target.value as TextToSpeechProvider)
					}
					disabled={executionLocked}
				>
					{TTS_PROVIDERS.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>

				<label className={styles.field} htmlFor="audio-voice">
					{t("pipeline.audioPanel.voice")}
				</label>
				<VoiceSelector
					voices={AVAILABLE_VOICES}
					value={voiceId}
					onChange={setVoiceId}
				/>

				{executionLocked ? (
					<p className={styles.error}>
						{t("pipeline.audioPanel.executionLocked")}
					</p>
				) : (
					<Button
						type="button"
						onClick={handleGenerate}
						disabled={generating || selectedTargets.length === 0}
					>
						{generating
							? t("pipeline.audioPanel.generating")
							: t("pipeline.audioPanel.generateCta")}
					</Button>
				)}

				{error ? <p className={styles.error}>{error}</p> : null}
			</Card>

			{audioEntries.length > 0 ? (
				<div className={styles.languageTabs}>
					{audioEntries.map((entry) => (
						<button
							key={entry.targetLanguage}
							type="button"
							className={
								entry.targetLanguage === activeLanguage
									? styles.activeTab
									: styles.tab
							}
							onClick={() => {
								setIsPlaying(false);
								setActiveLanguage(entry.targetLanguage);
							}}
						>
							{formatTranslationLanguageLabel(entry.targetLanguage)}
						</button>
					))}
				</div>
			) : null}

			{activeAudio && streamUrl ? (
				<Card className={styles.preview}>
					<div className={styles.previewHeader}>
						<p className={styles.sectionLabel}>
							{t("pipeline.audioPanel.preview")}
						</p>
						<Badge variant="neutral">
							{formatTextToSpeechProviderLabel(activeAudio.provider)}
						</Badge>
					</div>

					<p className={styles.voiceMeta}>
						{t("pipeline.audioPanel.voiceLabel")} {activeAudio.voiceDisplayName}
					</p>

					<audio ref={audioRef} src={streamUrl} preload="metadata">
						<track kind="captions" />
					</audio>

					<div className={styles.playerControls}>
						<Button type="button" onClick={togglePlayback}>
							{isPlaying
								? t("pipeline.audioPanel.pause")
								: t("pipeline.audioPanel.play")}
						</Button>
						<span>
							{t("pipeline.audioPanel.duration", {
								duration: formatAudioDuration(activeAudio.duration),
							})}
						</span>
						<a href={streamUrl} download className={styles.downloadLink}>
							{t("pipeline.audioPanel.downloadWav")}
						</a>
					</div>
				</Card>
			) : (
				<EmptyState
					title={t("pipeline.audioPanel.emptyTitle")}
					description={t("pipeline.audioPanel.emptyDescription")}
				/>
			)}
		</div>
	);
}
