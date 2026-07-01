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

	checkIntervention(
		videoId: string,
		sessionId: string,
		time: number,
	): Promise<ShadowInterventionCheck>;

	answerIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: AnswerShadowInterventionRequest,
	): Promise<ShadowInterventionAnswer>;

	skipIntervention(
		videoId: string,
		sessionId: string,
		interventionId: string,
		request: SkipShadowInterventionRequest,
	): Promise<ShadowInterventionCheck>;

	updateInterventionPolicy(
		videoId: string,
		sessionId: string,
		request: UpdateShadowInterventionPolicyRequest,
	): Promise<ShadowInterventionPolicy>;

	updateVoicePreference(
		videoId: string,
		sessionId: string,
		request: UpdateShadowVoicePreferenceRequest,
	): Promise<ShadowVoicePreference>;
}
