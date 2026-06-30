import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { API_BASE_URL } from "@/config/api";
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
	const { videoId = "" } = useParams();
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
		} catch {
			setError("Audio generation failed.");
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
				<Spinner label="Loading audio" />
			</div>
		);
	}

	if (!translationsAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title="Translation required"
					description="Generate translations before creating audio."
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>Audio Preview</h2>
					<p className={styles.meta}>Video ID: {videoId}</p>
				</div>
			</header>

			<Card className={styles.controls}>
				<p className={styles.sectionLabel}>Translation languages</p>
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

				<label className={styles.field} htmlFor="audio-provider">
					TTS engine
				</label>
				<select
					id="audio-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						setProvider(event.target.value as TextToSpeechProvider)
					}
				>
					{TTS_PROVIDERS.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>

				<label className={styles.field} htmlFor="audio-voice">
					Voice
				</label>
				<VoiceSelector
					voices={AVAILABLE_VOICES}
					value={voiceId}
					onChange={setVoiceId}
				/>

				<Button
					type="button"
					onClick={handleGenerate}
					disabled={generating || selectedTargets.length === 0}
				>
					{generating ? "Generating..." : "Generate Audio"}
				</Button>

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
						<p className={styles.sectionLabel}>Audio Preview</p>
						<Badge variant="neutral">
							{formatTextToSpeechProviderLabel(activeAudio.provider)}
						</Badge>
					</div>

					<p className={styles.voiceMeta}>
						Voice: {activeAudio.voiceDisplayName}
					</p>

					<audio ref={audioRef} src={streamUrl} preload="metadata">
						<track kind="captions" />
					</audio>

					<div className={styles.playerControls}>
						<Button type="button" onClick={togglePlayback}>
							{isPlaying ? "Pause" : "Play"}
						</Button>
						<span>Duration {formatAudioDuration(activeAudio.duration)}</span>
						<a href={streamUrl} download className={styles.downloadLink}>
							Download WAV
						</a>
					</div>
				</Card>
			) : (
				<EmptyState
					title="No audio yet"
					description="Select languages, choose a voice, and generate audio."
				/>
			)}
		</div>
	);
}
