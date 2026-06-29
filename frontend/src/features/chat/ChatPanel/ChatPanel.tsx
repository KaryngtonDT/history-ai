import { useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import type { Artifact } from "@/services/artifact/types";
import { EMPTY_CHAT_ANSWER } from "@/services/chat/types";
import { conversationService } from "@/services/conversation/ConversationService";
import type {
	Conversation,
	ConversationChatResult,
} from "@/services/conversation/types";
import { ChatInput } from "../ChatInput";
import { type ChatMessageItem, ChatMessageList } from "../ChatMessageList";
import {
	CHAT_ERROR_MESSAGE,
	CHAT_PANEL_TITLE,
	CHAT_STREAM_EMPTY_MESSAGE,
} from "../chatLabels";
import type { CitationClickDetails } from "../citationNavigation";
import { mapConversationToChatMessageItems } from "../conversationMessages";
import { type AvailableDocument, DocumentSelector } from "../DocumentSelector";
import styles from "./ChatPanel.module.css";

interface ChatPanelProps {
	contentId: string;
	artifacts: Artifact[];
	onCitationClick?: (details: CitationClickDetails) => void;
}

interface StreamingState {
	userMessage: string;
	assistantText: string;
}

function createConversationId(): string {
	return crypto.randomUUID();
}

function buildAvailableDocuments(
	contentId: string,
	artifacts: Artifact[],
): AvailableDocument[] {
	const contentIds = new Set<string>([contentId]);

	for (const artifact of artifacts) {
		contentIds.add(artifact.contentId);
	}

	return Array.from(contentIds).map((id) => ({
		contentId: id,
		label: id === contentId ? "This document" : id,
	}));
}

function buildChatResultFromConversation(
	conversation: Conversation,
	previousAnswer: ConversationChatResult["answer"] | null,
): ConversationChatResult {
	const lastAssistantMessage = conversation.messages.findLast(
		(message) => message.role === "assistant",
	);

	return {
		conversation,
		answer: previousAnswer ?? {
			...EMPTY_CHAT_ANSWER,
			answer: lastAssistantMessage?.text ?? "",
		},
	};
}

export function ChatPanel({
	contentId,
	artifacts,
	onCitationClick,
}: ChatPanelProps) {
	const [conversationId, setConversationId] = useState<string | null>(null);
	const [chatResult, setChatResult] = useState<ConversationChatResult | null>(
		null,
	);
	const [streamingState, setStreamingState] = useState<StreamingState | null>(
		null,
	);
	const [loading, setLoading] = useState(false);
	const [documentsUpdating, setDocumentsUpdating] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [question, setQuestion] = useState("");

	const activeContentId = chatResult?.conversation.contentId ?? contentId;

	const availableDocuments = useMemo(
		() => buildAvailableDocuments(contentId, artifacts),
		[contentId, artifacts],
	);

	const selectedContentIds = useMemo(() => {
		if (chatResult === null || chatResult.conversation.documents.length === 0) {
			return [contentId];
		}

		return chatResult.conversation.documents.map(
			(document) => document.contentId,
		);
	}, [chatResult, contentId]);

	const messages = useMemo<ChatMessageItem[]>(() => {
		const baseMessages =
			chatResult === null ? [] : mapConversationToChatMessageItems(chatResult);

		if (streamingState === null) {
			return baseMessages;
		}

		return [
			...baseMessages,
			{
				id: "streaming-user",
				role: "user" as const,
				content: streamingState.userMessage,
			},
			{
				id: "streaming-assistant",
				role: "assistant" as const,
				content: streamingState.assistantText,
			},
		];
	}, [chatResult, streamingState]);

	const artifactTypesById = useMemo(
		() =>
			Object.fromEntries(
				artifacts.map((artifact) => [artifact.id, artifact.type]),
			),
		[artifacts],
	);

	const hasConversation =
		chatResult !== null && chatResult.conversation.id !== "";

	function handleDocumentSelectionChange(contentIds: string[]): void {
		if (
			conversationId === null ||
			documentsUpdating ||
			contentIds.length === 0
		) {
			return;
		}

		setDocumentsUpdating(true);
		setError(null);

		void conversationService
			.updateDocuments(conversationId, contentIds)
			.then((conversation) => {
				if (conversation.id === "") {
					setError(CHAT_ERROR_MESSAGE);
					return;
				}

				setChatResult((current) =>
					current === null
						? current
						: {
								...current,
								conversation,
							},
				);
			})
			.catch(() => {
				setError(CHAT_ERROR_MESSAGE);
			})
			.finally(() => {
				setDocumentsUpdating(false);
			});
	}

	function handleStreamFailure(receivedConversation: boolean): void {
		setStreamingState(null);

		if (!receivedConversation) {
			setError(CHAT_STREAM_EMPTY_MESSAGE);
		}
	}

	function handleSubmit(): void {
		const trimmedQuestion = question.trim();

		if (trimmedQuestion === "" || loading) {
			return;
		}

		const activeConversationId = conversationId ?? createConversationId();

		if (conversationId === null) {
			setConversationId(activeConversationId);
		}

		setQuestion("");
		setLoading(true);
		setError(null);
		setStreamingState({
			userMessage: trimmedQuestion,
			assistantText: "",
		});

		let receivedConversation = false;

		void conversationService
			.streamQuestion(activeContentId, activeConversationId, trimmedQuestion, {
				onToken: (token) => {
					setStreamingState((current) =>
						current === null
							? current
							: {
									...current,
									assistantText: current.assistantText + token.text,
								},
					);
				},
				onConversation: (conversation) => {
					receivedConversation = true;
					setConversationId(conversation.id);
					setChatResult((current) =>
						buildChatResultFromConversation(
							conversation,
							current?.answer ?? null,
						),
					);
					setStreamingState(null);
				},
				onDone: () => {
					setLoading(false);

					if (!receivedConversation) {
						handleStreamFailure(false);
					}
				},
				onError: () => {
					setLoading(false);
					handleStreamFailure(receivedConversation);
				},
			})
			.catch(() => {
				setLoading(false);
				handleStreamFailure(receivedConversation);
			});
	}

	return (
		<Card className={styles.chatPanel}>
			<p className={styles.label}>{CHAT_PANEL_TITLE}</p>
			{hasConversation ? (
				<DocumentSelector
					availableDocuments={availableDocuments}
					selectedContentIds={selectedContentIds}
					onSelectionChange={handleDocumentSelectionChange}
					disabled={loading || documentsUpdating}
				/>
			) : null}
			<ChatMessageList
				messages={messages}
				artifactTypesById={artifactTypesById}
				onCitationClick={onCitationClick}
			/>
			{error !== null ? (
				<EmptyState
					className={styles.errorState}
					title="Unable to get an answer"
					description={error}
				/>
			) : null}
			<ChatInput
				value={question}
				onChange={setQuestion}
				onSubmit={handleSubmit}
				loading={loading}
				disabled={documentsUpdating}
			/>
		</Card>
	);
}
