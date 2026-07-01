import type { ShadowRepository } from "./ShadowRepository";
import type {
	AskShadowQuestionRequest,
	ShadowSession,
	StartShadowSessionRequest,
	WatchContext,
} from "./types";

const MOCK_VIDEO_ID = "550e8400-e29b-41d4-a716-446655440099";

function mockContext(time: number): WatchContext {
	return {
		videoId: MOCK_VIDEO_ID,
		currentTimeSeconds: time,
		targetLanguage: "fr",
		conversationId: null,
		currentTranscriptSegment: {
			index: 0,
			startTime: 0,
			endTime: 5,
			text: "Compound interest grows on prior interest.",
			translatedText:
				"Les intérêts composés croissent sur les intérêts antérieurs.",
		},
		currentTranslationSegment: {
			index: 0,
			startTime: 0,
			endTime: 5,
			text: "Compound interest grows on prior interest.",
			translatedText:
				"Les intérêts composés croissent sur les intérêts antérieurs.",
		},
		previousTranscriptSegment: null,
		nextTranscriptSegment: {
			index: 1,
			startTime: 5,
			endTime: 10,
			text: "This is the second segment.",
		},
		previousTranslationSegment: null,
		nextTranslationSegment: null,
		nearbyTranscriptContext:
			"Compound interest grows on prior interest. This is the second segment.",
		nearbyTranslationContext:
			"Les intérêts composés croissent sur les intérêts antérieurs.",
		currentSpeaker: null,
		recentInteractions: [],
		conversationMemory: [],
	};
}

export class MockShadowRepository implements ShadowRepository {
	private session: ShadowSession | null = null;

	async getContext(
		_videoId: string,
		time: number,
		_language: string,
	): Promise<WatchContext | null> {
		return mockContext(time);
	}

	async startSession(
		videoId: string,
		request: StartShadowSessionRequest,
	): Promise<ShadowSession> {
		this.session = {
			sessionId: crypto.randomUUID(),
			videoId,
			playbackState: "playing",
			targetLanguage: request.targetLanguage,
			currentTimeSeconds: 0,
			currentTranscriptSegmentIndex: null,
			currentTranslationSegmentIndex: null,
			contentId: request.contentId ?? null,
			conversationId: request.conversationId ?? null,
			interactions: [],
		};

		return this.session;
	}

	async askQuestion(
		videoId: string,
		sessionId: string,
		request: AskShadowQuestionRequest,
	) {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));
		const answer = `At ${request.time.toFixed(1)}s: ${request.question} — mock Shadow answer.`;

		const updated: ShadowSession = {
			...session,
			sessionId,
			currentTimeSeconds: request.time,
			interactions: [
				...session.interactions,
				{
					kind: "question",
					participant: "user",
					videoTimestamp: request.time,
					text: request.question,
				},
				{
					kind: "answer",
					participant: "shadow",
					videoTimestamp: request.time,
					text: answer,
				},
			],
		};

		this.session = updated;

		return {
			sessionId,
			answer,
			currentTimeSeconds: request.time,
			currentTranscriptSegmentIndex: 0,
			currentTranslationSegmentIndex: 0,
			session: updated,
		};
	}

	async pauseSession(videoId: string, sessionId: string, time?: number) {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));

		const updated: ShadowSession = {
			...session,
			sessionId,
			playbackState: "paused",
			currentTimeSeconds: time ?? session.currentTimeSeconds,
			interactions: [
				...session.interactions,
				{
					kind: "pause",
					participant: "user",
					videoTimestamp: time ?? session.currentTimeSeconds,
				},
			],
		};

		this.session = updated;
		return updated;
	}

	async resumeSession(videoId: string, sessionId: string, time?: number) {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));

		const updated: ShadowSession = {
			...session,
			sessionId,
			playbackState: "playing",
			currentTimeSeconds: time ?? session.currentTimeSeconds,
			interactions: [
				...session.interactions,
				{
					kind: "resume",
					participant: "user",
					videoTimestamp: time ?? session.currentTimeSeconds,
				},
			],
		};

		this.session = updated;
		return updated;
	}
}
