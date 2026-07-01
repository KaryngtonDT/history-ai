import type { ShadowRepository } from "./ShadowRepository";
import {
	type AnswerShadowInterventionRequest,
	type AskShadowQuestionRequest,
	DEFAULT_SHADOW_INTERVENTION_POLICY,
	type ShadowIntervention,
	type ShadowInterventionCheck,
	type ShadowInterventionPolicy,
	type ShadowSession,
	type SkipShadowInterventionRequest,
	type StartShadowSessionRequest,
	type UpdateShadowInterventionPolicyRequest,
	type WatchContext,
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
	private policy: ShadowInterventionPolicy = DEFAULT_SHADOW_INTERVENTION_POLICY;
	private pendingIntervention: ShadowIntervention | null = null;

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
			policy: this.policy,
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
			policy: session.policy,
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

	async checkIntervention(
		_videoId: string,
		sessionId: string,
		time: number,
	): Promise<ShadowInterventionCheck> {
		const session =
			this.session ??
			(await this.startSession(_videoId, { targetLanguage: "fr" }));

		if (!session.policy.enabled) {
			return {
				hasIntervention: false,
				intervention: null,
				recommendPause: false,
				recommendResume: false,
				session,
			};
		}

		if (this.pendingIntervention && !this.pendingIntervention.answered) {
			return {
				hasIntervention: true,
				intervention: this.pendingIntervention,
				recommendPause: session.policy.allowAutoPause,
				recommendResume: false,
				session,
			};
		}

		if (time < 3) {
			return {
				hasIntervention: false,
				intervention: null,
				recommendPause: false,
				recommendResume: false,
				session,
			};
		}

		const intervention: ShadowIntervention = {
			id: crypto.randomUUID(),
			type: "vocabulary_check",
			trigger: "unknown_vocabulary",
			reason: "Shadow noticed vocabulary that may need clarification.",
			videoTimestamp: time,
			expectedUserAction: "Answer the challenge or say skip",
			allowAutoPause: true,
			challenge: {
				questionText: "What does compound interest mean in this context?",
			},
			skipped: false,
			answered: false,
		};

		this.pendingIntervention = intervention;

		const updated: ShadowSession = {
			...session,
			sessionId,
			currentTimeSeconds: time,
			policy: session.policy,
		};
		this.session = updated;

		return {
			hasIntervention: true,
			intervention,
			recommendPause: session.policy.allowAutoPause,
			recommendResume: false,
			session: updated,
		};
	}

	async answerIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: AnswerShadowInterventionRequest,
	) {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));
		const reply = `Good thinking. ${request.answer}`;

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
					text: request.answer,
				},
				{
					kind: "answer",
					participant: "shadow",
					videoTimestamp: request.time,
					text: reply,
				},
			],
			policy: session.policy,
		};

		if (this.pendingIntervention?.id === interventionId) {
			this.pendingIntervention = {
				...this.pendingIntervention,
				answered: true,
			};
		}

		this.session = updated;

		return {
			sessionId,
			interventionId,
			reply,
			recommendResume: session.policy.autoResume,
			session: updated,
		};
	}

	async skipIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: SkipShadowInterventionRequest,
	): Promise<ShadowInterventionCheck> {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));

		if (this.pendingIntervention?.id === interventionId) {
			this.pendingIntervention = {
				...this.pendingIntervention,
				skipped: true,
			};
		}

		const updated: ShadowSession = {
			...session,
			sessionId,
			currentTimeSeconds: request.time,
			policy: session.policy,
		};
		this.session = updated;

		return {
			hasIntervention: false,
			intervention: null,
			recommendPause: false,
			recommendResume: false,
			session: updated,
		};
	}

	async updateInterventionPolicy(
		videoId: string,
		sessionId: string,
		request: UpdateShadowInterventionPolicyRequest,
	): Promise<ShadowInterventionPolicy> {
		const session =
			this.session ??
			(await this.startSession(videoId, { targetLanguage: "fr" }));

		let next = { ...session.policy };

		if (request.tutorMode === "off") {
			next = { ...next, enabled: false };
		} else if (request.tutorMode === "gentle") {
			next = {
				...next,
				enabled: true,
				challengeLevel: "easy",
				explanationStyle: "short",
				maxInterventionsPerMinute: 2,
				minSecondsBetweenInterventions: 45,
			};
		} else if (request.tutorMode === "normal") {
			next = {
				...next,
				enabled: true,
				challengeLevel: "normal",
				explanationStyle: "detailed",
				maxInterventionsPerMinute: 4,
				minSecondsBetweenInterventions: 30,
			};
		}

		if (request.enabled !== undefined) {
			next = { ...next, enabled: request.enabled };
		}
		if (request.challengeLevel) {
			next = { ...next, challengeLevel: request.challengeLevel };
		}
		if (request.explanationStyle) {
			next = { ...next, explanationStyle: request.explanationStyle };
		}
		if (request.maxInterventionsPerMinute !== undefined) {
			next = {
				...next,
				maxInterventionsPerMinute: request.maxInterventionsPerMinute,
			};
		}
		if (request.minSecondsBetweenInterventions !== undefined) {
			next = {
				...next,
				minSecondsBetweenInterventions: request.minSecondsBetweenInterventions,
			};
		}
		if (request.autoResume !== undefined) {
			next = { ...next, autoResume: request.autoResume };
		}
		if (request.allowAutoPause !== undefined) {
			next = { ...next, allowAutoPause: request.allowAutoPause };
		}

		this.policy = next;
		this.session = { ...session, sessionId, policy: next };

		return next;
	}
}
