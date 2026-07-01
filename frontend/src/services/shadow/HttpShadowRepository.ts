import {
	videoShadowContextPath,
	videoShadowSessionAskPath,
	videoShadowSessionPausePath,
	videoShadowSessionResumePath,
	videoShadowSessionsPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ShadowRepository } from "./ShadowRepository";
import {
	type AskShadowQuestionRequest,
	mapShadowAnswerFromApi,
	mapShadowSessionFromApi,
	mapWatchContextFromApi,
	type ShadowAnswerApiDto,
	type ShadowSessionApiDto,
	type StartShadowSessionRequest,
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
}
