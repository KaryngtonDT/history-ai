import type { ChatAnswer } from "@/services/chat/types";
import type { ConversationChatResult } from "@/services/conversation/types";
import type { ChatMessageItem } from "./ChatMessageList";

export function mapConversationToChatMessageItems(
	result: ConversationChatResult,
): ChatMessageItem[] {
	const { conversation, answer } = result;
	const lastAssistantIndex = conversation.messages.findLastIndex(
		(message) => message.role === "assistant",
	);

	return conversation.messages.map((message, index) => {
		const item: ChatMessageItem = {
			id: `${conversation.id}-${index}`,
			role: message.role,
			content: message.text,
		};

		if (index === lastAssistantIndex && message.role === "assistant") {
			return attachAnswerMetadata(item, answer);
		}

		return item;
	});
}

function attachAnswerMetadata(
	item: ChatMessageItem,
	answer: ChatAnswer,
): ChatMessageItem {
	return {
		...item,
		sources: answer.sources,
		citations: answer.citations,
	};
}
