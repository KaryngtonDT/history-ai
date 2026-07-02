import {
	videoShadowContextPath,
	videoShadowSessionAskPath,
	videoShadowSessionInterventionAnswerPath,
	videoShadowSessionInterventionPath,
	videoShadowSessionInterventionSkipPath,
	videoShadowSessionPausePath,
	videoShadowSessionPolicyPath,
	videoShadowSessionResumePath,
	videoShadowSessionsPath,
	videoShadowSessionVoicePath,
	videoShadowSessionLearningPath,
	videoShadowSessionStrategyPath,
	videoShadowSessionLearningPreferencesPath,
	videoShadowSessionLearningObservationsPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ShadowRepository } from "./ShadowRepository";
import {
	type AnswerShadowInterventionRequest,
	type AskShadowQuestionRequest,
	DEFAULT_SHADOW_INTERVENTION_POLICY,
	DEFAULT_SHADOW_VOICE_PREFERENCE,
	mapShadowAnswerFromApi,
	mapShadowSessionFromApi,
	mapWatchContextFromApi,
	type ShadowAnswerApiDto,
	type ShadowInterventionAnswer,
	type ShadowInterventionCheck,
	type ShadowInterventionPolicy,
	type ShadowSessionApiDto,
	type ShadowVoicePreference,
	type SkipShadowInterventionRequest,
	type StartShadowSessionRequest,
	type UpdateShadowInterventionPolicyRequest,
	type UpdateShadowVoicePreferenceRequest,
	type SessionLearningState,
	type SessionTeachingStrategy,
	type UpdateSessionLearningPreferencesRequest,
	type RecordSessionObservationRequest,
	type WatchContextApiDto,
} from "./types";

export class HttpShadowRepository implements ShadowRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getContext(
		videoId: string,
		time: number,
		language: string,
		conversationId?: string,
	) {
		try {
			const params = new URLSearchParams({
				time: String(time),
				language,
			});

			if (conversationId) {
				params.set("conversationId", conversationId);
			}

			const dto = await this.httpClient.get<WatchContextApiDto>(
				`${videoShadowContextPath(videoId)}?${params.toString()}`,
			);

			return mapWatchContextFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return null;
			}

			throw error;
		}
	}

	async startSession(videoId: string, request: StartShadowSessionRequest) {
		const dto = await this.httpClient.post<ShadowSessionApiDto>(
			videoShadowSessionsPath(videoId),
			request,
		);

		return mapShadowSessionFromApi(dto);
	}

	async askQuestion(
		videoId: string,
		sessionId: string,
		request: AskShadowQuestionRequest,
	) {
		const dto = await this.httpClient.post<ShadowAnswerApiDto>(
			videoShadowSessionAskPath(videoId, sessionId),
			request,
		);

		return mapShadowAnswerFromApi(dto);
	}

	async pauseSession(videoId: string, sessionId: string, time?: number) {
		const dto = await this.httpClient.post<ShadowSessionApiDto>(
			videoShadowSessionPausePath(videoId, sessionId),
			time === undefined ? {} : { time },
		);

		return mapShadowSessionFromApi(dto);
	}

	async resumeSession(videoId: string, sessionId: string, time?: number) {
		const dto = await this.httpClient.post<ShadowSessionApiDto>(
			videoShadowSessionResumePath(videoId, sessionId),
			time === undefined ? {} : { time },
		);

		return mapShadowSessionFromApi(dto);
	}

	async checkIntervention(videoId: string, sessionId: string, time: number) {
		const params = new URLSearchParams({ time: String(time) });
		const dto = await this.httpClient.get<ShadowInterventionCheck>(
			`${videoShadowSessionInterventionPath(videoId, sessionId)}?${params.toString()}`,
		);

		return {
			...dto,
			session: mapShadowSessionFromApi(dto.session),
		};
	}

	async answerIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: AnswerShadowInterventionRequest,
	) {
		const dto = await this.httpClient.post<ShadowInterventionAnswer>(
			videoShadowSessionInterventionAnswerPath(
				videoId,
				sessionId,
				interventionId,
			),
			request,
		);

		return {
			...dto,
			session: mapShadowSessionFromApi(dto.session),
		};
	}

	async skipIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: SkipShadowInterventionRequest,
	) {
		const dto = await this.httpClient.post<ShadowInterventionCheck>(
			videoShadowSessionInterventionSkipPath(
				videoId,
				sessionId,
				interventionId,
			),
			request,
		);

		return {
			...dto,
			session: mapShadowSessionFromApi(dto.session),
		};
	}

	async updateInterventionPolicy(
		videoId: string,
		sessionId: string,
		request: UpdateShadowInterventionPolicyRequest,
	) {
		const dto = await this.httpClient.put<{ policy: ShadowInterventionPolicy }>(
			videoShadowSessionPolicyPath(videoId, sessionId),
			request,
		);

		return dto.policy ?? DEFAULT_SHADOW_INTERVENTION_POLICY;
	}

	async updateVoicePreference(
		videoId: string,
		sessionId: string,
		request: UpdateShadowVoicePreferenceRequest,
	) {
		const dto = await this.httpClient.put<{
			voicePreference: ShadowVoicePreference;
		}>(videoShadowSessionVoicePath(videoId, sessionId), request);

		return dto.voicePreference ?? DEFAULT_SHADOW_VOICE_PREFERENCE;
	}

	async getSessionLearning(videoId: string, sessionId: string) {
		return this.httpClient.get<SessionLearningState>(
			videoShadowSessionLearningPath(videoId, sessionId),
		);
	}

	async getSessionStrategy(videoId: string, sessionId: string) {
		return this.httpClient.get<SessionTeachingStrategy>(
			videoShadowSessionStrategyPath(videoId, sessionId),
		);
	}

	async updateSessionLearningPreferences(
		videoId: string,
		sessionId: string,
		request: UpdateSessionLearningPreferencesRequest,
	) {
		return this.httpClient.put<SessionLearningState>(
			videoShadowSessionLearningPreferencesPath(videoId, sessionId),
			request,
		);
	}

	async recordSessionObservation(
		videoId: string,
		sessionId: string,
		request: RecordSessionObservationRequest,
	) {
		return this.httpClient.post<SessionLearningState>(
			videoShadowSessionLearningObservationsPath(videoId, sessionId),
			request,
		);
	}
}
