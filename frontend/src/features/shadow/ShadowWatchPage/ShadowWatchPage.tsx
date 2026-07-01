import { useCallback, useEffect, useRef, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { API_BASE_URL } from "@/config/api";
import { useTranslation } from "@/i18n/useTranslation";
import { resolveVideoRenderStreamUrl } from "@/services/render/types";
import { videoRenderService } from "@/services/render/VideoRenderService";
import { shadowService } from "@/services/shadow/ShadowService";
import type { ShadowSession, WatchContext } from "@/services/shadow/types";
import { transcriptService } from "@/services/transcript/TranscriptService";
import type { VideoTranscript } from "@/services/transcript/types";
import { CurrentContextCard } from "../CurrentContextCard";
import { ShadowControls } from "../ShadowControls";
import { ShadowConversation } from "../ShadowConversation";
import { ShadowPlayer } from "../ShadowPlayer";
import { ShadowTranscriptPanel } from "../ShadowTranscriptPanel";
import { ShadowTranslationPanel } from "../ShadowTranslationPanel";
import { ShadowVoiceButton, speakShadowAnswer } from "../ShadowVoiceButton";
import { VocabularyPanel } from "../VocabularyPanel";
import styles from "./ShadowWatchPage.module.css";

const CONTEXT_DEBOUNCE_MS = 400;
const DEFAULT_TARGET_LANGUAGE = "fr";

export function ShadowWatchPage() {
	const { videoId = "" } = useParams();
	const { t } = useTranslation();
	const videoRef = useRef<HTMLVideoElement | null>(null);
	const debounceRef = useRef<number | null>(null);

	const [loading, setLoading] = useState(true);
	const [streamUrl, setStreamUrl] = useState<string | null>(null);
	const [session, setSession] = useState<ShadowSession | null>(null);
	const [context, setContext] = useState<WatchContext | null>(null);
	const [transcript, setTranscript] = useState<VideoTranscript | null>(null);
	const [currentTime, setCurrentTime] = useState(0);
	const [question, setQuestion] = useState("");
	const [isBusy, setIsBusy] = useState(false);
	const [transcriptCollapsed, setTranscriptCollapsed] = useState(false);
	const [translationCollapsed, setTranslationCollapsed] = useState(false);

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

			const [renders, transcriptResult] = await Promise.all([
				videoRenderService.listRenders(videoId),
				transcriptService.getTranscript(videoId),
			]);

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

			const startedSession = await shadowService.startSession(videoId, {
				targetLanguage: DEFAULT_TARGET_LANGUAGE,
				contentId: videoId,
			});

			if (!cancelled) {
				setSession(startedSession);
				await refreshContext(0, startedSession);
				setLoading(false);
			}
		}

		void bootstrap();

		return () => {
			cancelled = true;
		};
	}, [videoId, refreshContext]);

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
				},
			);

			setSession(result.session);
			setQuestion("");
			speakShadowAnswer(result.answer);
		} finally {
			setIsBusy(false);
		}
	};

	if (loading) {
		return <Spinner label={t("pipeline.shadow.loading")} />;
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
					<ShadowVoiceButton onTranscript={(text) => setQuestion(text)} />
				</div>

				<div className={styles.side}>
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
