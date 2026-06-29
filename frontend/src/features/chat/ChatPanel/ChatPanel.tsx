import { useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import type { Artifact } from "@/services/artifact/types";
import { conversationService } from "@/services/conversation/ConversationService";
import type { ConversationChatResult } from "@/services/conversation/types";
import { ChatInput } from "../ChatInput";
import { type ChatMessageItem, ChatMessageList } from "../ChatMessageList";
import {
	CHAT_EMPTY_ANSWER_MESSAGE,
	CHAT_ERROR_MESSAGE,
	CHAT_PANEL_TITLE,
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

export function ChatPanel({
	contentId,
	artifacts,
	onCitationClick,
}: ChatPanelProps) {
	const [conversationId, setConversationId] = useState<string | null>(null);
	const [chatResult, setChatResult] = useState<ConversationChatResult | null>(
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
		if (chatResult === null) {
			return [];
		}

		return mapConversationToChatMessageItems(chatResult);
	}, [chatResult]);

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

		void conversationService
			.askQuestion(activeContentId, activeConversationId, trimmedQuestion)
			.then((result) => {
				if (
					result.conversation.id === "" ||
					result.conversation.messages.length === 0 ||
					result.answer.answer.trim() === ""
				) {
					setError(CHAT_EMPTY_ANSWER_MESSAGE);
					return;
				}

				setConversationId(result.conversation.id);
				setChatResult(result);
			})
			.catch(() => {
				setError(CHAT_ERROR_MESSAGE);
			})
			.finally(() => {
				setLoading(false);
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
