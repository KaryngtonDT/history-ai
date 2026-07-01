import type {
	AskShadowQuestionRequest,
	ShadowAnswer,
	ShadowSession,
	StartShadowSessionRequest,
	WatchContext,
} from "./types";

export interface ShadowRepository {
	getContext(
		videoId: string,
		time: number,
		language: string,
		conversationId?: string,
	): Promise<WatchContext | null>;

	startSession(
		videoId: string,
		request: StartShadowSessionRequest,
	): Promise<ShadowSession>;

	askQuestion(
		videoId: string,
		sessionId: string,
		request: AskShadowQuestionRequest,
	): Promise<ShadowAnswer>;

	pauseSession(
		videoId: string,
		sessionId: string,
		time?: number,
	): Promise<ShadowSession>;

	resumeSession(
		videoId: string,
		sessionId: string,
		time?: number,
	): Promise<ShadowSession>;
}
