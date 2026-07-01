export interface WatchContextSegment {
	index: number;
	startTime: number;
	endTime: number;
	text: string;
	translatedText?: string;
}

export interface ShadowInteraction {
	kind: string;
	participant: string;
	videoTimestamp: number;
	text?: string;
}

export interface WatchContext {
	videoId: string;
	currentTimeSeconds: number;
	targetLanguage: string;
	conversationId: string | null;
	currentTranscriptSegment: WatchContextSegment | null;
	currentTranslationSegment: WatchContextSegment | null;
	previousTranscriptSegment: WatchContextSegment | null;
	nextTranscriptSegment: WatchContextSegment | null;
	previousTranslationSegment: WatchContextSegment | null;
	nextTranslationSegment: WatchContextSegment | null;
	nearbyTranscriptContext: string;
	nearbyTranslationContext: string;
	currentSpeaker: string | null;
	recentInteractions: ShadowInteraction[];
	conversationMemory: string[];
}

export interface ShadowSession {
	sessionId: string;
	videoId: string;
	playbackState: "playing" | "paused" | "ended";
	targetLanguage: string;
	currentTimeSeconds: number;
	currentTranscriptSegmentIndex: number | null;
	currentTranslationSegmentIndex: number | null;
	contentId: string | null;
	conversationId: string | null;
	interactions: ShadowInteraction[];
}

export interface ShadowAnswer {
	sessionId: string;
	answer: string;
	currentTimeSeconds: number;
	currentTranscriptSegmentIndex: number | null;
	currentTranslationSegmentIndex: number | null;
	session: ShadowSession;
}

export interface StartShadowSessionRequest {
	targetLanguage: string;
	contentId?: string;
	conversationId?: string;
}

export interface AskShadowQuestionRequest {
	question: string;
	time: number;
}

export type WatchContextApiDto = WatchContext;
export type ShadowSessionApiDto = ShadowSession;
export type ShadowAnswerApiDto = ShadowAnswer;

export function mapWatchContextFromApi(dto: WatchContextApiDto): WatchContext {
	return dto;
}

export function mapShadowSessionFromApi(
	dto: ShadowSessionApiDto,
): ShadowSession {
	return dto;
}

export function mapShadowAnswerFromApi(dto: ShadowAnswerApiDto): ShadowAnswer {
	return dto;
}
