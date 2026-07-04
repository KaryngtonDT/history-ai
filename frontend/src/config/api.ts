import { env } from "./env";

export const API_BASE_URL = env.apiBaseUrl;

export const CONTENTS_PATH = "/api/contents";

export const PROCESSING_JOBS_PATH = "/api/processing-jobs";

export const LIBRARY_ITEMS_PATH = "/api/library/items";

export const COLLECTIONS_PATH = "/api/collections";

export const PROJECTS_PATH = "/api/projects";

export function projectPath(projectId: string): string {
	return `${PROJECTS_PATH}/${projectId}`;
}

export function projectProcessPath(projectId: string): string {
	return `${PROJECTS_PATH}/${projectId}/process`;
}

export function projectVideosPath(projectId: string): string {
	return `${PROJECTS_PATH}/${projectId}/videos`;
}

export function projectVideoPath(projectId: string, videoId: string): string {
	return `${PROJECTS_PATH}/${projectId}/videos/${videoId}`;
}

export function videoHistoryPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/history`;
}

export function videoHistoryVersionPath(
	videoId: string,
	version: number,
): string {
	return `${VIDEOS_PATH}/${videoId}/history/${version}`;
}

export function videoHistoryComparePath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/history/compare`;
}

export function videoHistoryReprocessPath(
	videoId: string,
	version: number,
): string {
	return `${VIDEOS_PATH}/${videoId}/history/${version}/reprocess`;
}

export const SEARCH_LIBRARY_PATH = "/api/search/library";

export const VIDEOS_PATH = "/api/videos";

export const AUDIO_PATH = "/api/audio";

export const YOUTUBE_PATH = "/api/youtube";

export const PREFERENCES_PATH = "/api/preferences";

export const LEARNING_PROFILE_PATH = "/api/learning/profile";
export const LEARNING_RECOMMENDATIONS_PATH = "/api/learning/recommendations";
export const LEARNING_SIGNALS_PATH = "/api/learning/signals";
export const LEARNING_RESET_PATH = "/api/learning/reset";
export const LEARNING_PREFERENCES_PATH = "/api/learning/preferences";

export const SHADOW_VOICE_LIBRARY_PATH = "/api/shadow/voice/library";
export const SHADOW_VOICE_COLLECTIONS_PATH = "/api/shadow/voice/collections";
export const SHADOW_VOICE_PREVIEW_PATH = "/api/shadow/voice/preview";
export const SHADOW_VOICE_PRESET_PATH = "/api/shadow/voice/preset";

export const SHADOW_IDENTITY_PROFILE_PATH = "/api/shadow/identity/profile";
export const SHADOW_IDENTITY_PREFERENCES_PATH =
	"/api/shadow/identity/preferences";
export const SHADOW_IDENTITY_RESET_PATH = "/api/shadow/identity/reset";
export const SHADOW_IDENTITY_CONFIGURE_PATH = "/api/shadow/identity/configure";

export const SHADOW_RELATIONSHIP_PROFILE_PATH =
	"/api/shadow/relationship/profile";
export const SHADOW_RELATIONSHIP_PORTRAIT_PATH =
	"/api/shadow/relationship/portrait";
export const SHADOW_RELATIONSHIP_TIMELINE_PATH =
	"/api/shadow/relationship/timeline";
export const SHADOW_RELATIONSHIP_INTERESTS_PATH =
	"/api/shadow/relationship/interests";
export const SHADOW_RELATIONSHIP_SIGNALS_PATH =
	"/api/shadow/relationship/signals";
export const SHADOW_RELATIONSHIP_PREFERENCES_PATH =
	"/api/shadow/relationship/preferences";
export const SHADOW_RELATIONSHIP_RESET_PATH = "/api/shadow/relationship/reset";
export const SHADOW_RELATIONSHIP_CONFIGURE_PATH =
	"/api/shadow/relationship/configure";
export function SHADOW_RELATIONSHIP_APPROVE_CHANGE_PATH(
	changeId: string,
): string {
	return `/api/shadow/relationship/changes/${changeId}/approve`;
}
export function SHADOW_RELATIONSHIP_REJECT_CHANGE_PATH(
	changeId: string,
): string {
	return `/api/shadow/relationship/changes/${changeId}/reject`;
}

export const SHADOW_MEMORY_TIMELINE_PATH = "/api/shadow/memory/timeline";
export const SHADOW_MEMORY_CONCEPTS_PATH = "/api/shadow/memory/concepts";
export const SHADOW_MEMORY_VOCABULARY_PATH = "/api/shadow/memory/vocabulary";
export const SHADOW_MEMORY_MILESTONES_PATH = "/api/shadow/memory/milestones";
export const SHADOW_MEMORY_CONNECTIONS_PATH = "/api/shadow/memory/connections";
export const SHADOW_MEMORY_JOURNEY_PATH = "/api/shadow/memory/journey";
export const SHADOW_MEMORY_SEARCH_PATH = "/api/shadow/memory/search";
export const SHADOW_MEMORY_RESET_PATH = "/api/shadow/memory/reset";

export const SHADOW_TEACHING_PATH_PATH = "/api/shadow/teaching/path";
export const SHADOW_TEACHING_CURRENT_PATH = "/api/shadow/teaching/current";
export const SHADOW_TEACHING_OBJECTIVES_PATH =
	"/api/shadow/teaching/objectives";
export const SHADOW_TEACHING_REVISIONS_PATH = "/api/shadow/teaching/revisions";
export const SHADOW_TEACHING_EXERCISES_PATH = "/api/shadow/teaching/exercises";
export function SHADOW_TEACHING_EXERCISE_ANSWER_PATH(
	exerciseId: string,
): string {
	return `/api/shadow/teaching/exercise/${exerciseId}/answer`;
}
export function SHADOW_TEACHING_CHECKPOINT_COMPLETE_PATH(
	checkpointId: string,
): string {
	return `/api/shadow/teaching/checkpoint/${checkpointId}/complete`;
}
export const SHADOW_TEACHING_PREFERENCES_PATH =
	"/api/shadow/teaching/preferences";
export const SHADOW_TEACHING_RESET_PATH = "/api/shadow/teaching/reset";

export const SHADOW_KNOWLEDGE_GRAPH_PATH = "/api/shadow/knowledge/graph";
export function SHADOW_KNOWLEDGE_NODE_PATH(id: string): string {
	return `/api/shadow/knowledge/node/${id}`;
}
export const SHADOW_KNOWLEDGE_PATH_PATH = "/api/shadow/knowledge/path";
export const SHADOW_KNOWLEDGE_GAPS_PATH = "/api/shadow/knowledge/gaps";
export const SHADOW_KNOWLEDGE_RELATED_PATH = "/api/shadow/knowledge/related";
export const SHADOW_KNOWLEDGE_SEARCH_PATH = "/api/shadow/knowledge/search";
export const SHADOW_KNOWLEDGE_REBUILD_PATH = "/api/shadow/knowledge/rebuild";
export const SHADOW_KNOWLEDGE_RESET_PATH = "/api/shadow/knowledge/reset";

export const SHADOW_GOALS_PATH = "/api/shadow/goals";
export const SHADOW_GOALS_RESET_PATH = "/api/shadow/goals/reset";
export function shadowGoalPath(id: string): string {
	return `/api/shadow/goals/${id}`;
}
export const SHADOW_MENTOR_PATH = "/api/shadow/mentor";
export const SHADOW_MISSIONS_PATH = "/api/shadow/missions";
export function SHADOW_MISSION_COMPLETE_PATH(missionId: string): string {
	return `/api/shadow/missions/${missionId}/complete`;
}
export const SHADOW_ROADMAP_PATH = "/api/shadow/roadmap";

export const SHADOW_EXECUTIVE_PATH = "/api/shadow/executive";
export const SHADOW_EXECUTIVE_AGENDA_PATH = "/api/shadow/executive/agenda";
export const SHADOW_EXECUTIVE_RECOMMENDATIONS_PATH =
	"/api/shadow/executive/recommendations";
export const SHADOW_EXECUTIVE_HISTORY_PATH = "/api/shadow/executive/history";
export const SHADOW_EXECUTIVE_RESET_PATH = "/api/shadow/executive/reset";
export function shadowExecutiveDecisionApprovePath(id: string): string {
	return `/api/shadow/executive/decision/${id}/approve`;
}
export function shadowExecutiveDecisionRejectPath(id: string): string {
	return `/api/shadow/executive/decision/${id}/reject`;
}
export function shadowExecutiveDecisionDeferPath(id: string): string {
	return `/api/shadow/executive/decision/${id}/defer`;
}

export const SHADOW_BRAIN_PATH = "/api/shadow/brain";
export const SHADOW_BRAIN_CONCEPTS_PATH = "/api/shadow/brain/concepts";
export const SHADOW_BRAIN_SEARCH_PATH = "/api/shadow/brain/search";
export const SHADOW_BRAIN_TIMELINE_PATH = "/api/shadow/brain/timeline";
export const SHADOW_BRAIN_DIFF_PATH = "/api/shadow/brain/diff";
export const SHADOW_BRAIN_BOOKMARK_PATH = "/api/shadow/brain/bookmark";
export const SHADOW_BRAIN_NOTE_PATH = "/api/shadow/brain/note";
export const SHADOW_BRAIN_REBUILD_PATH = "/api/shadow/brain/rebuild";
export function shadowBrainConceptPath(id: string): string {
	return `/api/shadow/brain/concept/${id}`;
}
export function shadowBrainBookmarkPath(id: string): string {
	return `/api/shadow/brain/bookmark/${id}`;
}

export const SHADOW_PRESENCE_SESSION_PATH = "/api/shadow/presence/session";
export const SHADOW_PRESENCE_CONNECT_PATH = "/api/shadow/presence/connect";
export const SHADOW_PRESENCE_DISCONNECT_PATH =
	"/api/shadow/presence/disconnect";
export const SHADOW_PRESENCE_CONTEXT_PATH = "/api/shadow/presence/context";
export const SHADOW_PRESENCE_PREFERENCES_PATH =
	"/api/shadow/presence/preferences";
export const SHADOW_PRESENCE_HISTORY_PATH = "/api/shadow/presence/history";
export const SHADOW_PRESENCE_EXPLAIN_PATH = "/api/shadow/presence/explain";

export const SHADOW_BROWSER_SESSION_PATH = "/api/shadow/browser/session";
export const SHADOW_BROWSER_CONNECT_PATH = "/api/shadow/browser/connect";
export const SHADOW_BROWSER_DISCONNECT_PATH = "/api/shadow/browser/disconnect";
export const SHADOW_BROWSER_CONTEXT_PATH = "/api/shadow/browser/context";
export const SHADOW_BROWSER_PLATFORM_PATH = "/api/shadow/browser/platform";
export const SHADOW_BROWSER_PERMISSIONS_PATH =
	"/api/shadow/browser/permissions";
export const SHADOW_BROWSER_HISTORY_PATH = "/api/shadow/browser/history";
export const SHADOW_BROWSER_EXPLAIN_PATH = "/api/shadow/browser/explain";

export const SHADOW_MOBILE_PROFILE_PATH = "/api/shadow/mobile/profile";
export const SHADOW_MOBILE_TODAY_PATH = "/api/shadow/mobile/today";
export const SHADOW_MOBILE_MISSIONS_PATH = "/api/shadow/mobile/missions";
export const SHADOW_MOBILE_REVISIONS_PATH = "/api/shadow/mobile/revisions";
export const SHADOW_MOBILE_SERVER_PATH = "/api/shadow/mobile/server";
export const SHADOW_MOBILE_HEALTH_PATH = "/api/shadow/mobile/health";
export const SHADOW_MOBILE_CONNECTIONS_PATH = "/api/shadow/mobile/connections";
export const SHADOW_MOBILE_DEVICE_PATH = "/api/shadow/mobile/device";
export const SHADOW_MOBILE_SYNC_PATH = "/api/shadow/mobile/sync";
export const SHADOW_MOBILE_PREFERENCES_PATH = "/api/shadow/mobile/preferences";
export const SHADOW_MOBILE_CONNECTION_PATH = "/api/shadow/mobile/connection";
export const SHADOW_MOBILE_PUSH_TOKEN_PATH = "/api/shadow/mobile/push-token";

export function videoReviewsPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/reviews`;
}

export const AI_PROVIDERS_PATH = "/api/ai/providers";

export const PIPELINE_PATH = "/api/pipeline";

export const ORCHESTRATOR_RECOMMENDATION_PATH =
	"/api/orchestrator/recommendation";

export function videoIntelligencePath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/intelligence`;
}

export function videoOptimizationPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/optimization`;
}

export function videoSchedulePath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/schedule`;
}

export function videoQualityPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/quality`;
}

export function pipelineResetPath(): string {
	return `${PIPELINE_PATH}/reset`;
}

export function videoTranscriptPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/transcript`;
}

export function videoStatusPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/status`;
}

export function videoProcessPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/process`;
}

export function videoTranslationsPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/translations`;
}

export function videoTranslationPath(
	videoId: string,
	language: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/translations/${language}`;
}

export function videoAudioListPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/audio`;
}

export function videoAudioPath(videoId: string, language: string): string {
	return `${VIDEOS_PATH}/${videoId}/audio/${language}`;
}

export function videoAudioStreamPath(
	videoId: string,
	language: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/audio/${language}/stream`;
}

export function videoVoiceCloneListPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/voice-clone`;
}

export function videoVoiceClonePath(videoId: string, language: string): string {
	return `${VIDEOS_PATH}/${videoId}/voice-clone/${language}`;
}

export function videoVoiceCloneStreamPath(
	videoId: string,
	language: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/voice-clone/${language}/stream`;
}

export function videoLipSyncListPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/lip-sync`;
}

export function videoLipSyncPath(videoId: string, language: string): string {
	return `${VIDEOS_PATH}/${videoId}/lip-sync/${language}`;
}

export function videoLipSyncStreamPath(
	videoId: string,
	language: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/lip-sync/${language}/stream`;
}

export function videoRenderListPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/render`;
}

export function videoRenderPath(videoId: string, language: string): string {
	return `${VIDEOS_PATH}/${videoId}/render/${language}`;
}

export function videoRenderStreamPath(
	videoId: string,
	language: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/render/${language}/stream`;
}

export function videoShadowContextPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/context`;
}

export function videoShadowSessionsPath(videoId: string): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions`;
}

export function videoShadowSessionAskPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/ask`;
}

export function videoShadowSessionPausePath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/pause`;
}

export function videoShadowSessionResumePath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/resume`;
}

export function videoShadowSessionInterventionPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/intervention`;
}

export function videoShadowSessionInterventionAnswerPath(
	videoId: string,
	sessionId: string,
	interventionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/intervention/${interventionId}/answer`;
}

export function videoShadowSessionInterventionSkipPath(
	videoId: string,
	sessionId: string,
	interventionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/intervention/${interventionId}/skip`;
}

export function videoShadowSessionPolicyPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/policy`;
}

export function videoShadowSessionVoicePath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/voice`;
}

export function videoShadowSessionLearningPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/learning`;
}

export function videoShadowSessionStrategyPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/strategy`;
}

export function videoShadowSessionLearningPreferencesPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/learning/preferences`;
}

export function videoShadowSessionLearningObservationsPath(
	videoId: string,
	sessionId: string,
): string {
	return `${VIDEOS_PATH}/${videoId}/shadow/sessions/${sessionId}/learning/observations`;
}

export function timelinePath(artifactId: string): string {
	return `/api/timeline/${artifactId}`;
}

export function timelineMapPath(artifactId: string): string {
	return `/api/maps/timeline/${artifactId}`;
}

export function contentArtifactsPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts`;
}

export function contentRelationsPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/relations`;
}

export function contentGraphPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/graph`;
}

export function contentGraphArtifactNeighborhoodPath(
	contentId: string,
	artifactId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/graph/artifacts/${artifactId}/neighborhood`;
}

export function contentArtifactRecommendationsPath(
	contentId: string,
	artifactId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts/${artifactId}/recommendations`;
}

export function contentSemanticSearchPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/semantic-search`;
}

export function contentChatPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/chat`;
}

export function contentChatStreamPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/chat/stream`;
}

export function contentConversationChatPath(
	contentId: string,
	conversationId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/conversations/${conversationId}/chat`;
}

export function contentConversationChatStreamPath(
	contentId: string,
	conversationId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/conversations/${conversationId}/chat/stream`;
}

export function conversationDocumentsPath(conversationId: string): string {
	return `/api/conversations/${conversationId}/documents`;
}

export function conversationGraphPath(conversationId: string): string {
	return `/api/conversations/${conversationId}/graph`;
}

export function contentAgentRunPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/agent/run`;
}

export function collectionItemsPath(collectionId: string): string {
	return `${COLLECTIONS_PATH}/${collectionId}/items`;
}

export function workspaceMembersPath(workspaceId: string): string {
	return `/api/workspaces/${workspaceId}/members`;
}

export function workspaceMemberPath(
	workspaceId: string,
	memberId: string,
): string {
	return `/api/workspaces/${workspaceId}/members/${memberId}`;
}

export function workspaceInvitationsPath(workspaceId: string): string {
	return `/api/workspaces/${workspaceId}/invitations`;
}

export function workspaceAnalyticsPath(workspaceId: string): string {
	return `/api/workspaces/${workspaceId}/analytics`;
}

export function workspaceProvidersPath(workspaceId: string): string {
	return `/api/workspaces/${workspaceId}/providers`;
}

export function workspaceTelemetryPath(workspaceId: string): string {
	return `/api/workspaces/${workspaceId}/telemetry`;
}
