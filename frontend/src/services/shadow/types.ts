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
	policy: ShadowInterventionPolicy;
	voicePreference: ShadowVoicePreference;
}

export interface ShadowVoicePreference {
	mode: ShadowVoiceMode;
	manualLanguage: ShadowVoiceLanguageCode | null;
}

export type ShadowVoiceMode =
	| "same_as_interface"
	| "same_as_target_language"
	| "manual";

export type ShadowVoiceLanguageCode = "en" | "fr" | "de";

export const DEFAULT_SHADOW_VOICE_PREFERENCE: ShadowVoicePreference = {
	mode: "same_as_target_language",
	manualLanguage: null,
};

export interface ShadowInterventionPolicy {
	enabled: boolean;
	maxInterventionsPerMinute: number;
	minSecondsBetweenInterventions: number;
	challengeLevel: ShadowChallengeLevel;
	explanationStyle: ShadowExplanationStyle;
	autoResume: boolean;
	allowAutoPause: boolean;
}

export type ShadowChallengeLevel = "easy" | "normal" | "hard";
export type ShadowExplanationStyle = "short" | "detailed" | "example_first";
export type ShadowTutorMode = "off" | "gentle" | "normal";
export type ShadowInterventionFrequency = "low" | "normal" | "high";

export interface ShadowChallenge {
	questionText: string;
	suggestedAnswer?: string;
}

export interface ShadowIntervention {
	id: string;
	type: string;
	trigger: string;
	reason: string;
	videoTimestamp: number;
	expectedUserAction: string;
	allowAutoPause: boolean;
	explanation?: string;
	challenge?: ShadowChallenge;
	skipped: boolean;
	answered: boolean;
}

export interface ShadowInterventionCheck {
	hasIntervention: boolean;
	intervention: ShadowIntervention | null;
	recommendPause: boolean;
	recommendResume: boolean;
	session: ShadowSession;
}

export interface ShadowInterventionAnswer {
	sessionId: string;
	interventionId: string;
	reply: string;
	recommendResume: boolean;
	answerLanguage: string;
	speechLanguage: string;
	fallbackUsed: boolean;
	reason: string;
	session: ShadowSession;
}

export interface AnswerShadowInterventionRequest {
	answer: string;
	time: number;
}

export interface SkipShadowInterventionRequest {
	time: number;
}

export interface UpdateShadowInterventionPolicyRequest {
	tutorMode?: ShadowTutorMode;
	enabled?: boolean;
	challengeLevel?: ShadowChallengeLevel;
	explanationStyle?: ShadowExplanationStyle;
	maxInterventionsPerMinute?: number;
	minSecondsBetweenInterventions?: number;
	autoResume?: boolean;
	allowAutoPause?: boolean;
}

export interface UpdateShadowVoicePreferenceRequest {
	mode?: ShadowVoiceMode;
	manualLanguage?: ShadowVoiceLanguageCode;
}

export const DEFAULT_SHADOW_INTERVENTION_POLICY: ShadowInterventionPolicy = {
	enabled: false,
	maxInterventionsPerMinute: 2,
	minSecondsBetweenInterventions: 45,
	challengeLevel: "easy",
	explanationStyle: "short",
	autoResume: false,
	allowAutoPause: true,
};

export interface ShadowAnswer {
	sessionId: string;
	answer: string;
	currentTimeSeconds: number;
	currentTranscriptSegmentIndex: number | null;
	currentTranslationSegmentIndex: number | null;
	answerLanguage: string;
	speechLanguage: string;
	fallbackUsed: boolean;
	reason: string;
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
	interfaceLanguage?: string;
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
	return {
		...dto,
		policy: dto.policy ?? DEFAULT_SHADOW_INTERVENTION_POLICY,
		voicePreference: dto.voicePreference ?? DEFAULT_SHADOW_VOICE_PREFERENCE,
	};
}

export function mapShadowAnswerFromApi(dto: ShadowAnswerApiDto): ShadowAnswer {
	return dto;
}
