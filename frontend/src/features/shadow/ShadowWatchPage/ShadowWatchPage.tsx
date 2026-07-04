import { useCallback, useEffect, useRef, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { API_BASE_URL } from "@/config/api";
import { KnowledgeDiffPanel } from "@/features/shadowBrain/KnowledgeDiffPanel";
import { ExecutiveWatchBar } from "@/features/shadowExecutive/ExecutiveWatchBar";
import { useTranslation } from "@/i18n/useTranslation";
import { resolveVideoRenderStreamUrl } from "@/services/render/types";
import { videoRenderService } from "@/services/render/VideoRenderService";
import { shadowService } from "@/services/shadow/ShadowService";
import type {
	SessionLearningState,
	SessionTeachingStrategy,
	ShadowChallengeLevel,
	ShadowExplanationStyle,
	ShadowIntervention,
	ShadowInterventionFrequency,
	ShadowSession,
	ShadowTutorMode,
	WatchContext,
} from "@/services/shadow/types";
import { transcriptService } from "@/services/transcript/TranscriptService";
import type { VideoTranscript } from "@/services/transcript/types";
import { CurrentContextCard } from "../CurrentContextCard";
import { ShadowControls } from "../ShadowControls";
import { ShadowConversation } from "../ShadowConversation";
import { ShadowInterventionCard } from "../ShadowInterventionCard";
import { ShadowKnowledgePanel } from "../ShadowKnowledgePanel";
import { ShadowLearningPanel } from "../ShadowLearningPanel";
import { ShadowMentorPanel } from "../ShadowMentorPanel";
import { ShadowPlayer } from "../ShadowPlayer";
import { ShadowResumePrompt } from "../ShadowResumePrompt";
import { ShadowTeachingPanel } from "../ShadowTeachingPanel";
import { ShadowTranscriptPanel } from "../ShadowTranscriptPanel";
import { ShadowTranslationPanel } from "../ShadowTranslationPanel";
import { ShadowTutorBadge } from "../ShadowTutorBadge";
import { ShadowTutorSettings } from "../ShadowTutorSettings";
import { ShadowVoiceButton } from "../ShadowVoiceButton";
import { ShadowVoiceSettings } from "../ShadowVoiceSettings";
import { policyWithFrequency } from "../shadowTutorPolicy";
import {
	effectiveSpeechLanguage,
	resolveSpeechLanguageFromAnswer,
	type ShadowSpeechLanguage,
	selectedLanguageFromPreference,
	speakShadowAnswer,
	toVoicePreferencePayload,
	voiceRateForStrategy,
} from "../shadowVoice";
import { VocabularyPanel } from "../VocabularyPanel";
import styles from "./ShadowWatchPage.module.css";

const CONTEXT_DEBOUNCE_MS = 400;
const INTERVENTION_DEBOUNCE_MS = 900;
const LEARNING_POLL_MS = 5000;
const DEFAULT_TARGET_LANGUAGE = "fr";
const TRANSCRIPT_POLL_INTERVAL_MS = 3000;
const TRANSCRIPT_WAIT_TIMEOUT_MS = 5 * 60 * 1000;

function sleep(ms: number): Promise<void> {
	return new Promise((resolve) => {
		window.setTimeout(resolve, ms);
	});
}

export function ShadowWatchPage() {
	const { videoId = "" } = useParams();
	const { t, locale } = useTranslation();
	const videoRef = useRef<HTMLVideoElement | null>(null);
	const debounceRef = useRef<number | null>(null);
	const interventionDebounceRef = useRef<number | null>(null);
	const interventionBusyRef = useRef(false);

	const [loading, setLoading] = useState(true);
	const [loadingLabel, setLoadingLabel] = useState<string | undefined>(
		undefined,
	);
	const [streamUrl, setStreamUrl] = useState<string | null>(null);
	const [session, setSession] = useState<ShadowSession | null>(null);
	const [context, setContext] = useState<WatchContext | null>(null);
	const [transcript, setTranscript] = useState<VideoTranscript | null>(null);
	const [currentTime, setCurrentTime] = useState(0);
	const [question, setQuestion] = useState("");
	const [isBusy, setIsBusy] = useState(false);
	const [transcriptCollapsed, setTranscriptCollapsed] = useState(false);
	const [translationCollapsed, setTranslationCollapsed] = useState(false);
	const [activeIntervention, setActiveIntervention] =
		useState<ShadowIntervention | null>(null);
	const [interventionAnswer, setInterventionAnswer] = useState("");
	const [interventionReply, setInterventionReply] = useState<string | null>(
		null,
	);
	const [showResumePrompt, setShowResumePrompt] = useState(false);
	const [sessionLearning, setSessionLearning] =
		useState<SessionLearningState | null>(null);
	const [sessionStrategy, setSessionStrategy] =
		useState<SessionTeachingStrategy | null>(null);

	const refreshContext = useCallback(
		async (time: number, activeSession: ShadowSession) => {
			const nextContext = await shadowService.getContext(
				videoId,
				time,
				activeSession.targetLanguage,
				activeSession.conversationId ?? undefined,
			);

			setContext(nextContext);
		},
		[videoId],
	);

	useEffect(() => {
		let cancelled = false;

		async function bootstrap() {
			setLoading(true);
			setLoadingLabel(undefined);

			try {
				let transcriptResult = await transcriptService.getTranscript(videoId);
				const renders = await videoRenderService.listRenders(videoId);

				if (cancelled) {
					return;
				}

				setTranscript(transcriptResult);

				const firstRender = renders[0] ?? null;

				if (firstRender) {
					const render = await videoRenderService.getRender(
						videoId,
						firstRender.targetLanguage,
					);

					if (render && !cancelled) {
						setStreamUrl(
							resolveVideoRenderStreamUrl(render.streamUrl, API_BASE_URL),
						);
					}
				}

				const deadline = Date.now() + TRANSCRIPT_WAIT_TIMEOUT_MS;

				while (!cancelled) {
					try {
						const startedSession = await shadowService.startSession(videoId, {
							targetLanguage: DEFAULT_TARGET_LANGUAGE,
							contentId: videoId,
						});

						setSession(startedSession);
						await refreshContext(0, startedSession);
						return;
					} catch {
						if (transcriptResult !== null) {
							return;
						}

						if (Date.now() >= deadline) {
							return;
						}

						setLoadingLabel(t("pipeline.shadow.preparingTranscript"));
						await sleep(TRANSCRIPT_POLL_INTERVAL_MS);

						if (cancelled) {
							return;
						}

						transcriptResult =
							await transcriptService.getTranscript(videoId);
						setTranscript(transcriptResult);
					}
				}
			} finally {
				if (!cancelled) {
					setLoading(false);
					setLoadingLabel(undefined);
				}
			}
		}

		void bootstrap();

		return () => {
			cancelled = true;
		};
	}, [videoId, refreshContext, t]);

	const refreshSessionLearning = useCallback(
		async (activeSession: ShadowSession) => {
			const [learning, strategy] = await Promise.all([
				shadowService.getSessionLearning(videoId, activeSession.sessionId),
				shadowService.getSessionStrategy(videoId, activeSession.sessionId),
			]);

			setSessionLearning(learning);
			setSessionStrategy(strategy);
		},
		[videoId],
	);

	useEffect(() => {
		if (!session) {
			return;
		}

		void refreshSessionLearning(session);

		const intervalId = window.setInterval(() => {
			void refreshSessionLearning(session);
		}, LEARNING_POLL_MS);

		return () => {
			window.clearInterval(intervalId);
		};
	}, [session, refreshSessionLearning]);

	const handleTimeUpdate = (time: number) => {
		setCurrentTime(time);

		if (!session) {
			return;
		}

		if (debounceRef.current !== null) {
			window.clearTimeout(debounceRef.current);
		}

		debounceRef.current = window.setTimeout(() => {
			void refreshContext(time, session);
		}, CONTEXT_DEBOUNCE_MS);

		if (
			!session.policy.enabled ||
			activeIntervention ||
			interventionBusyRef.current ||
			session.playbackState !== "playing"
		) {
			return;
		}

		if (interventionDebounceRef.current !== null) {
			window.clearTimeout(interventionDebounceRef.current);
		}

		interventionDebounceRef.current = window.setTimeout(() => {
			void checkIntervention(time, session);
		}, INTERVENTION_DEBOUNCE_MS);
	};

	const checkIntervention = async (
		time: number,
		activeSession: ShadowSession,
	) => {
		if (interventionBusyRef.current || !activeSession.policy.enabled) {
			return;
		}

		interventionBusyRef.current = true;

		try {
			const result = await shadowService.checkIntervention(
				videoId,
				activeSession.sessionId,
				time,
			);

			setSession(result.session);

			if (result.hasIntervention && result.intervention) {
				setActiveIntervention(result.intervention);
				setInterventionAnswer("");
				setInterventionReply(null);
				setShowResumePrompt(false);

				if (result.recommendPause && result.session.policy.allowAutoPause) {
					videoRef.current?.pause();
				}
			}
		} finally {
			interventionBusyRef.current = false;
		}
	};

	const handlePolicyChange = async (update: {
		tutorMode?: ShadowTutorMode;
		challengeLevel?: ShadowChallengeLevel;
		explanationStyle?: ShadowExplanationStyle;
		frequency?: ShadowInterventionFrequency;
		autoResume?: boolean;
		allowAutoPause?: boolean;
	}) => {
		if (!session) {
			return;
		}

		setIsBusy(true);

		try {
			const frequencyPolicy = update.frequency
				? policyWithFrequency(session.policy, update.frequency)
				: null;

			const policy = await shadowService.updateInterventionPolicy(
				videoId,
				session.sessionId,
				{
					tutorMode: update.tutorMode,
					challengeLevel: update.challengeLevel,
					explanationStyle: update.explanationStyle,
					autoResume: update.autoResume,
					allowAutoPause: update.allowAutoPause,
					maxInterventionsPerMinute: frequencyPolicy?.maxInterventionsPerMinute,
					minSecondsBetweenInterventions:
						frequencyPolicy?.minSecondsBetweenInterventions,
				},
			);

			setSession({ ...session, policy });

			if (!policy.enabled) {
				setActiveIntervention(null);
				setInterventionReply(null);
				setShowResumePrompt(false);
			}
		} finally {
			setIsBusy(false);
		}
	};

	const speakingLanguage: ShadowSpeechLanguage = session
		? selectedLanguageFromPreference(session.voicePreference)
		: "auto";

	const speechRate = voiceRateForStrategy(
		sessionStrategy?.speakingPace ?? sessionLearning?.speakingPace,
		sessionStrategy?.voiceStyle ?? sessionLearning?.voiceStyle,
	);

	const handleVoiceLanguageChange = async (language: ShadowSpeechLanguage) => {
		if (!session) {
			return;
		}

		setIsBusy(true);

		try {
			const voicePreference = await shadowService.updateVoicePreference(
				videoId,
				session.sessionId,
				toVoicePreferencePayload(language),
			);

			setSession({ ...session, voicePreference });
		} finally {
			setIsBusy(false);
		}
	};

	const handleInterventionAnswer = async () => {
		if (!session || !activeIntervention || interventionAnswer.trim() === "") {
			return;
		}

		setIsBusy(true);

		try {
			const result = await shadowService.answerIntervention(
				videoId,
				session.sessionId,
				activeIntervention.id,
				{
					answer: interventionAnswer.trim(),
					time: currentTime,
				},
			);

			setSession(result.session);
			setInterventionReply(result.reply);
			setInterventionAnswer("");
			speakShadowAnswer(
				result.reply,
				resolveSpeechLanguageFromAnswer(result.speechLanguage),
				speechRate,
			);
			setShowResumePrompt(result.recommendResume);
		} finally {
			setIsBusy(false);
		}
	};

	const handleInterventionSkip = async () => {
		if (!session || !activeIntervention) {
			return;
		}

		setIsBusy(true);

		try {
			const result = await shadowService.skipIntervention(
				videoId,
				session.sessionId,
				activeIntervention.id,
				{ time: currentTime },
			);

			setSession(result.session);
			setActiveIntervention(null);
			setInterventionAnswer("");
			setInterventionReply(null);
			setShowResumePrompt(false);

			if (session.policy.autoResume) {
				await videoRef.current?.play();
			}
		} finally {
			setIsBusy(false);
		}
	};

	const handleContinueWatching = async () => {
		setActiveIntervention(null);
		setInterventionReply(null);
		setShowResumePrompt(false);
		await videoRef.current?.play();
	};

	const handlePause = async () => {
		if (!session) {
			return;
		}

		setIsBusy(true);

		try {
			videoRef.current?.pause();
			const updated = await shadowService.pauseSession(
				videoId,
				session.sessionId,
				currentTime,
			);
			setSession(updated);
			await shadowService.recordSessionObservation(videoId, session.sessionId, {
				type: "pause",
				timeSeconds: currentTime,
			});
			await refreshSessionLearning(updated);
		} finally {
			setIsBusy(false);
		}
	};

	const handleResume = async () => {
		if (!session) {
			return;
		}

		setIsBusy(true);

		try {
			const updated = await shadowService.resumeSession(
				videoId,
				session.sessionId,
				currentTime,
			);
			setSession(updated);
			await videoRef.current?.play();
		} finally {
			setIsBusy(false);
		}
	};

	const handleAdaptiveToggle = async (enabled: boolean) => {
		if (!session) {
			return;
		}

		const learning = await shadowService.updateSessionLearningPreferences(
			videoId,
			session.sessionId,
			{ adaptiveEnabled: enabled },
		);
		setSessionLearning(learning);
	};

	const handleAsk = async () => {
		if (!session || question.trim() === "") {
			return;
		}

		setIsBusy(true);

		try {
			const result = await shadowService.askQuestion(
				videoId,
				session.sessionId,
				{
					question: question.trim(),
					time: currentTime,
					interfaceLanguage: locale,
				},
			);

			setSession(result.session);
			setQuestion("");
			speakShadowAnswer(
				result.answer,
				resolveSpeechLanguageFromAnswer(result.speechLanguage),
				speechRate,
			);
			await refreshSessionLearning(result.session);
		} finally {
			setIsBusy(false);
		}
	};

	if (loading) {
		return (
			<Spinner label={loadingLabel ?? t("pipeline.shadow.loading")} />
		);
	}

	if (!session) {
		return (
			<EmptyState
				title={t("pipeline.shadow.emptyTitle")}
				description={t("pipeline.shadow.emptyDescription")}
			/>
		);
	}

	return (
		<div className={styles.page}>
			<header className={styles.header}>
				<div>
					<p className={styles.eyebrow}>{t("pipeline.shadow.eyebrow")}</p>
					<h1 className={styles.title}>{t("pipeline.shadow.title")}</h1>
					<p className={styles.description}>
						{t("pipeline.shadow.description")}
					</p>
					<ShadowTutorBadge enabled={session.policy.enabled} />
				</div>
				<ShadowControls
					playbackState={session.playbackState}
					onPause={() => void handlePause()}
					onResume={() => void handleResume()}
					isBusy={isBusy}
				/>
			</header>

			<div className={styles.layout}>
				<div className={styles.primary}>
					{streamUrl ? (
						<ShadowPlayer
							streamUrl={streamUrl}
							videoRef={videoRef}
							onTimeUpdate={handleTimeUpdate}
						/>
					) : (
						<EmptyState
							title={t("pipeline.shadow.noVideoTitle")}
							description={t("pipeline.shadow.noVideoDescription")}
						/>
					)}
					<CurrentContextCard
						currentTime={currentTime}
						segment={context?.currentTranscriptSegment ?? null}
					/>
					<ShadowVoiceButton
						onTranscript={(text) => setQuestion(text)}
						speechLanguage={speakingLanguage}
						targetLanguage={session.targetLanguage}
					/>
				</div>

				<div className={styles.side}>
					<ShadowVoiceSettings
						selectedLanguage={speakingLanguage}
						disabled={isBusy}
						onChange={(language) => void handleVoiceLanguageChange(language)}
					/>
					<ShadowTutorSettings
						policy={session.policy}
						disabled={isBusy}
						onChange={(update) => void handlePolicyChange(update)}
					/>
					<ShadowLearningPanel
						learning={sessionLearning}
						strategy={sessionStrategy}
						disabled={isBusy}
						onAdaptiveToggle={(enabled) => void handleAdaptiveToggle(enabled)}
					/>
					<ShadowTeachingPanel />
					<ShadowKnowledgePanel />
					<ShadowMentorPanel />
					<ExecutiveWatchBar />
					<KnowledgeDiffPanel resourceType="video" resourceId={videoId} />
					{activeIntervention ? (
						<ShadowInterventionCard
							intervention={activeIntervention}
							answer={interventionAnswer}
							reply={interventionReply}
							isBusy={isBusy}
							speechLanguage={effectiveSpeechLanguage(
								speakingLanguage,
								session.targetLanguage,
							)}
							onAnswerChange={setInterventionAnswer}
							onSubmitAnswer={() => void handleInterventionAnswer()}
							onSkip={() => void handleInterventionSkip()}
						/>
					) : null}
					{showResumePrompt ? (
						<ShadowResumePrompt
							disabled={isBusy}
							onResume={() => void handleContinueWatching()}
						/>
					) : null}
					<ShadowConversation
						interactions={session.interactions}
						question={question}
						isAsking={isBusy}
						onQuestionChange={setQuestion}
						onSubmit={() => void handleAsk()}
					/>
				</div>
			</div>

			<div className={styles.panels}>
				<ShadowTranscriptPanel
					transcript={transcript}
					activeSegment={context?.currentTranscriptSegment ?? null}
					collapsed={transcriptCollapsed}
					onToggle={() => setTranscriptCollapsed((value) => !value)}
				/>
				<ShadowTranslationPanel
					segment={context?.currentTranslationSegment ?? null}
					nearbyContext={context?.nearbyTranslationContext ?? ""}
					collapsed={translationCollapsed}
					onToggle={() => setTranslationCollapsed((value) => !value)}
				/>
				<VocabularyPanel />
			</div>
		</div>
	);
}
