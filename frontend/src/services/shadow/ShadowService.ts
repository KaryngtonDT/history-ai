import type { ShadowRepository } from "./ShadowRepository";
import { createShadowRepository } from "./ShadowRepositoryFactory";
import type {
	AnswerShadowInterventionRequest,
	AskShadowQuestionRequest,
	ShadowAnswer,
	ShadowInterventionAnswer,
	ShadowInterventionCheck,
	ShadowInterventionPolicy,
	ShadowSession,
	ShadowVoicePreference,
	SkipShadowInterventionRequest,
	StartShadowSessionRequest,
	UpdateShadowInterventionPolicyRequest,
	UpdateShadowVoicePreferenceRequest,
	WatchContext,
} from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class ShadowService {
	private readonly repository: ShadowRepository;

	constructor(repository: ShadowRepository) {
		this.repository = repository;
	}

	getContext(
		videoId: string,
		time: number,
		language: string,
		conversationId?: string,
	): Promise<WatchContext | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getContext(
			videoId.trim(),
			time,
			language,
			conversationId,
		);
	}

	startSession(
		videoId: string,
		request: StartShadowSessionRequest,
	): Promise<ShadowSession> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		return this.repository.startSession(videoId.trim(), request);
	}

	askQuestion(
		videoId: string,
		sessionId: string,
		request: AskShadowQuestionRequest,
	): Promise<ShadowAnswer> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.askQuestion(videoId.trim(), sessionId, request);
	}

	pauseSession(
		videoId: string,
		sessionId: string,
		time?: number,
	): Promise<ShadowSession> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.pauseSession(videoId.trim(), sessionId, time);
	}

	resumeSession(
		videoId: string,
		sessionId: string,
		time?: number,
	): Promise<ShadowSession> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.resumeSession(videoId.trim(), sessionId, time);
	}

	checkIntervention(
		videoId: string,
		sessionId: string,
		time: number,
	): Promise<ShadowInterventionCheck> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.checkIntervention(videoId.trim(), sessionId, time);
	}

	answerIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: AnswerShadowInterventionRequest,
	): Promise<ShadowInterventionAnswer> {
		if (
			!this.isValidVideoId(videoId) ||
			sessionId.trim() === "" ||
			interventionId.trim() === ""
		) {
			return Promise.reject(new Error("Invalid shadow intervention request"));
		}

		return this.repository.answerIntervention(
			videoId.trim(),
			sessionId,
			interventionId,
			request,
		);
	}

	skipIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: SkipShadowInterventionRequest,
	): Promise<ShadowInterventionCheck> {
		if (
			!this.isValidVideoId(videoId) ||
			sessionId.trim() === "" ||
			interventionId.trim() === ""
		) {
			return Promise.reject(new Error("Invalid shadow intervention request"));
		}

		return this.repository.skipIntervention(
			videoId.trim(),
			sessionId,
			interventionId,
			request,
		);
	}

	updateInterventionPolicy(
		videoId: string,
		sessionId: string,
		request: UpdateShadowInterventionPolicyRequest,
	): Promise<ShadowInterventionPolicy> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.updateInterventionPolicy(
			videoId.trim(),
			sessionId,
			request,
		);
	}

	updateVoicePreference(
		videoId: string,
		sessionId: string,
		request: UpdateShadowVoicePreferenceRequest,
	): Promise<ShadowVoicePreference> {
		if (!this.isValidVideoId(videoId) || sessionId.trim() === "") {
			return Promise.reject(new Error("Invalid shadow session request"));
		}

		return this.repository.updateVoicePreference(
			videoId.trim(),
			sessionId,
			request,
		);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const shadowService = new ShadowService(createShadowRepository());
