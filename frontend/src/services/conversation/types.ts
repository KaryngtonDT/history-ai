import {
	type ChatAnswer,
	type ChatAnswerApiDto,
	EMPTY_CHAT_ANSWER,
	mapChatAnswerFromApi,
} from "@/services/chat/types";

export type ConversationMessageRole = "user" | "assistant";

export interface ConversationMessage {
	role: ConversationMessageRole;
	text: string;
}

export interface SelectedDocument {
	contentId: string;
}

export interface Conversation {
	id: string;
	contentId: string;
	messages: ConversationMessage[];
	documents: SelectedDocument[];
}

export interface ConversationChatResult {
	conversation: Conversation;
	answer: ChatAnswer;
}

export interface ConversationMessageApiDto {
	role: string;
	text: string;
}

export interface SelectedDocumentApiDto {
	contentId: string;
}

export interface ConversationApiDto {
	id: string;
	contentId: string;
	messages: ConversationMessageApiDto[];
	documents?: SelectedDocumentApiDto[];
}

export interface ConversationChatApiDto {
	conversation: ConversationApiDto;
	answer: ChatAnswerApiDto;
}

export interface UpdateConversationDocumentsApiDto {
	conversation: ConversationApiDto;
}

export const EMPTY_CONVERSATION: Conversation = {
	id: "",
	contentId: "",
	messages: [],
	documents: [],
};

export const EMPTY_CONVERSATION_CHAT_RESULT: ConversationChatResult = {
	conversation: EMPTY_CONVERSATION,
	answer: EMPTY_CHAT_ANSWER,
};

const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

const CONVERSATION_ID_PATTERN = CONTENT_ID_PATTERN;

function isConversationMessageRole(
	role: string,
): role is ConversationMessageRole {
	return role === "user" || role === "assistant";
}

export function mapConversationMessageFromApi(
	dto: ConversationMessageApiDto,
): ConversationMessage | null {
	if (!isConversationMessageRole(dto.role)) {
		return null;
	}

	if (typeof dto.text !== "string" || dto.text.trim() === "") {
		return null;
	}

	return {
		role: dto.role,
		text: dto.text,
	};
}

export function mapSelectedDocumentFromApi(
	dto: SelectedDocumentApiDto,
): SelectedDocument | null {
	if (
		typeof dto.contentId !== "string" ||
		!CONTENT_ID_PATTERN.test(dto.contentId)
	) {
		return null;
	}

	return {
		contentId: dto.contentId,
	};
}

function mapDocumentsFromApi(dto: ConversationApiDto): SelectedDocument[] {
	if (!Array.isArray(dto.documents)) {
		return [{ contentId: dto.contentId }];
	}

	const documents = dto.documents
		.map(mapSelectedDocumentFromApi)
		.filter((document): document is SelectedDocument => document !== null);

	if (documents.length === 0) {
		return [{ contentId: dto.contentId }];
	}

	return documents;
}

export function mapConversationFromApi(
	dto: ConversationApiDto,
): Conversation | null {
	if (
		typeof dto.id !== "string" ||
		!CONVERSATION_ID_PATTERN.test(dto.id) ||
		typeof dto.contentId !== "string" ||
		!CONTENT_ID_PATTERN.test(dto.contentId) ||
		!Array.isArray(dto.messages)
	) {
		return null;
	}

	const messages = dto.messages
		.map(mapConversationMessageFromApi)
		.filter((message): message is ConversationMessage => message !== null);

	return {
		id: dto.id,
		contentId: dto.contentId,
		messages,
		documents: mapDocumentsFromApi(dto),
	};
}

export function mapConversationChatFromApi(
	dto: ConversationChatApiDto,
): ConversationChatResult | null {
	const conversation = mapConversationFromApi(dto.conversation);

	if (conversation === null) {
		return null;
	}

	return {
		conversation,
		answer: mapChatAnswerFromApi(dto.answer),
	};
}

export function mapUpdateConversationDocumentsFromApi(
	dto: UpdateConversationDocumentsApiDto,
): Conversation | null {
	if (!dto || typeof dto !== "object" || !("conversation" in dto)) {
		return null;
	}

	return mapConversationFromApi(dto.conversation);
}

export function isValidContentId(contentId: string): boolean {
	return CONTENT_ID_PATTERN.test(contentId);
}

export function isValidConversationId(conversationId: string): boolean {
	return CONVERSATION_ID_PATTERN.test(conversationId);
}
