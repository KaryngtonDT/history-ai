import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { API_BASE_URL } from "@/config/api";
import { useTranslation } from "@/i18n/useTranslation";
import { audioService } from "@/services/audio/AudioService";
import { translationService } from "@/services/translation/TranslationService";
import {
	formatTranslationLanguageLabel,
	TARGET_TRANSLATION_LANGUAGES,
	type TranslationLanguage,
} from "@/services/translation/types";
import {
	formatVoiceCloneDuration,
	formatVoiceCloneProviderLabel,
	resolveVoiceCloneStreamUrl,
	type VideoVoiceClone,
	VOICE_CLONE_PROVIDERS,
	type VoiceCloneProvider,
	type VoiceMode,
} from "@/services/voice/types";
import { voiceCloneService } from "@/services/voice/VoiceCloneService";
import { VoiceModeSelector } from "../VoiceModeSelector";
import styles from "./VoiceClonePanel.module.css";

export function VoiceClonePanel() {
	const { t } = useTranslation();
	const { videoId = "" } = useParams();
	const originalRef = useRef<HTMLAudioElement | null>(null);
	const clonedRef = useRef<HTMLAudioElement | null>(null);
	const [translationsAvailable, setTranslationsAvailable] = useState(false);
	const [genericAudioAvailable, setGenericAudioAvailable] = useState(false);
	const [cloneEntries, setCloneEntries] = useState<VideoVoiceClone[]>([]);
	const [selectedTargets, setSelectedTargets] = useState<TranslationLanguage[]>(
		["french"],
	);
	const [voiceMode, setVoiceMode] = useState<VoiceMode>("clone");
	const [provider, setProvider] = useState<VoiceCloneProvider>("openvoice");
	const [activeLanguage, setActiveLanguage] =
		useState<TranslationLanguage | null>(null);
	const [loading, setLoading] = useState(true);
	const [generating, setGenerating] = useState(false);
	const [playingOriginal, setPlayingOriginal] = useState(false);
	const [playingCloned, setPlayingCloned] = useState(false);
	const [compareMode, setCompareMode] = useState(true);
	const [error, setError] = useState<string | null>(null);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const summaries = await translationService.listTranslations(videoId);
		setTranslationsAvailable(summaries.length > 0);

		const audioSummaries = await audioService.listAudio(videoId);
		setGenericAudioAvailable(audioSummaries.length > 0);

		const cloneSummaries = await voiceCloneService.listVoiceClones(videoId);
		const loadedClones = await Promise.all(
			cloneSummaries.map((summary) =>
				voiceCloneService.getVoiceClone(videoId, summary.targetLanguage),
			),
		);

		const available = loadedClones.filter(
			(entry): entry is VideoVoiceClone => entry !== null,
		);

		setCloneEntries(available);
		setActiveLanguage(
			(current) => current ?? available[0]?.targetLanguage ?? null,
		);
		setLoading(false);
	}, [videoId]);

	useEffect(() => {
		void loadData();
	}, [loadData]);

	const activeClone =
		cloneEntries.find((entry) => entry.targetLanguage === activeLanguage) ??
		null;

	const originalStreamUrl = useMemo(() => {
		if (!activeClone) {
			return null;
		}

		return resolveVoiceCloneStreamUrl(
			activeClone.originalAudioUrl,
			API_BASE_URL,
		);
	}, [activeClone]);

	const clonedStreamUrl = useMemo(() => {
		if (!activeClone) {
			return null;
		}

		return resolveVoiceCloneStreamUrl(activeClone.clonedAudioUrl, API_BASE_URL);
	}, [activeClone]);

	const toggleTargetLanguage = (language: TranslationLanguage) => {
		setSelectedTargets((current) =>
			current.includes(language)
				? current.filter((entry) => entry !== language)
				: [...current, language],
		);
	};

	const handleGenerate = async () => {
		if (voiceMode !== "clone") {
			setError(t("pipeline.voiceClone.invalidModeError"));
			return;
		}

		setGenerating(true);
		setError(null);

		try {
			await voiceCloneService.generateVoiceClone(videoId, {
				targetLanguages: selectedTargets,
				provider,
				voiceMode,
			});
			await loadData();
		} catch {
			setError(t("pipeline.voiceClone.failed"));
		} finally {
			setGenerating(false);
		}
	};

	const toggleOriginalPlayback = async () => {
		const element = originalRef.current;

		if (!element) {
			return;
		}

		if (element.paused) {
			clonedRef.current?.pause();
			setPlayingCloned(false);
			await element.play();
			setPlayingOriginal(true);
			return;
		}

		element.pause();
		setPlayingOriginal(false);
	};

	const toggleClonedPlayback = async () => {
		const element = clonedRef.current;

		if (!element) {
			return;
		}

		if (element.paused) {
			originalRef.current?.pause();
			setPlayingOriginal(false);
			await element.play();
			setPlayingCloned(true);
			return;
		}

		element.pause();
		setPlayingCloned(false);
	};

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.voiceClone.loading")} />
			</div>
		);
	}

	if (!translationsAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.voiceClone.requiredTranslationTitle")}
					description={t("pipeline.voiceClone.requiredTranslationDescription")}
				/>
			</div>
		);
	}

	if (!genericAudioAvailable) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.voiceClone.requiredAudioTitle")}
					description={t("pipeline.voiceClone.requiredAudioDescription")}
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.voiceClone.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.voiceClone.videoId")} {videoId}
					</p>
				</div>
			</header>

			<Card className={styles.controls}>
				<VoiceModeSelector value={voiceMode} onChange={setVoiceMode} />

				<p className={styles.sectionLabel}>
					{t("pipeline.voiceClone.translationLanguages")}
				</p>
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

				<label className={styles.field} htmlFor="voice-clone-provider">
					{t("pipeline.voiceClone.engine")}
				</label>
				<select
					id="voice-clone-provider"
					className={styles.select}
					value={provider}
					onChange={(event) =>
						setProvider(event.target.value as VoiceCloneProvider)
					}
					disabled={voiceMode !== "clone"}
				>
					{VOICE_CLONE_PROVIDERS.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>

				<Button
					type="button"
					onClick={handleGenerate}
					disabled={
						generating || selectedTargets.length === 0 || voiceMode !== "clone"
					}
				>
					{generating
						? t("pipeline.voiceClone.generating")
						: t("pipeline.voiceClone.generateCta")}
				</Button>

				{error ? <p className={styles.error}>{error}</p> : null}
			</Card>

			{cloneEntries.length > 0 ? (
				<div className={styles.languageTabs}>
					{cloneEntries.map((entry) => (
						<button
							key={entry.targetLanguage}
							type="button"
							className={
								entry.targetLanguage === activeLanguage
									? styles.activeTab
									: styles.tab
							}
							onClick={() => {
								setPlayingOriginal(false);
								setPlayingCloned(false);
								setActiveLanguage(entry.targetLanguage);
							}}
						>
							{formatTranslationLanguageLabel(entry.targetLanguage)}
						</button>
					))}
				</div>
			) : null}

			{activeClone && originalStreamUrl && clonedStreamUrl ? (
				<Card className={styles.preview}>
					<div className={styles.previewHeader}>
						<p className={styles.sectionLabel}>
							{t("pipeline.voiceClone.preview")}
						</p>
						<Badge variant="neutral">
							{formatVoiceCloneProviderLabel(activeClone.provider)}
						</Badge>
					</div>

					<label className={styles.compareToggle}>
						<input
							type="checkbox"
							checked={compareMode}
							onChange={(event) => setCompareMode(event.target.checked)}
						/>
						{t("pipeline.voiceClone.compareMode")}
					</label>

					<div
						className={compareMode ? styles.compareGrid : styles.singleColumn}
					>
						<div className={styles.playerBlock}>
							<p className={styles.playerLabel}>
								{t("pipeline.voiceClone.originalGeneric")}
							</p>
							<audio
								ref={originalRef}
								src={originalStreamUrl}
								preload="metadata"
							>
								<track kind="captions" />
							</audio>
							<Button type="button" onClick={toggleOriginalPlayback}>
								{playingOriginal
									? t("pipeline.voiceClone.pause")
									: t("pipeline.voiceClone.play")}
							</Button>
						</div>

						<div className={styles.playerBlock}>
							<p className={styles.playerLabel}>
								{t("pipeline.voiceClone.cloned")}
							</p>
							<audio ref={clonedRef} src={clonedStreamUrl} preload="metadata">
								<track kind="captions" />
							</audio>
							<Button type="button" onClick={toggleClonedPlayback}>
								{playingCloned
									? t("pipeline.voiceClone.pause")
									: t("pipeline.voiceClone.play")}
							</Button>
						</div>
					</div>

					<p className={styles.durationMeta}>
						{t("pipeline.voiceClone.duration", {
							duration: formatVoiceCloneDuration(activeClone.duration),
						})}
					</p>
				</Card>
			) : (
				<EmptyState
					title={t("pipeline.voiceClone.emptyTitle")}
					description={t("pipeline.voiceClone.emptyDescription")}
				/>
			)}
		</div>
	);
}
