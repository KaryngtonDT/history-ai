export const CHAT_PANEL_TITLE = "Chat with this document";

export const CHAT_INPUT_LABEL = "Ask a question";

export const CHAT_INPUT_PLACEHOLDER = "Ask a question...";

export const CHAT_SEND_BUTTON_LABEL = "Send";

export const CHAT_SOURCES_TITLE = "Sources";

export const CHAT_USER_PREFIX = "You";

export const CHAT_ASSISTANT_PREFIX = "Assistant";

export const CHAT_LOADING_LABEL = "Generating answer";

export const CHAT_ERROR_MESSAGE =
	"Something went wrong while asking this content. Please try again.";

export const CHAT_UNAVAILABLE_TITLE = "Chat unavailable";

export const CHAT_UNAVAILABLE_DESCRIPTION =
	"A valid content identifier is required before you can ask questions about this document.";

export const CHAT_EMPTY_ANSWER_MESSAGE =
	"No answer was returned for this question. Check that the content identifier is valid.";

export function formatChatScore(score: number): string {
	return score.toFixed(2);
}
