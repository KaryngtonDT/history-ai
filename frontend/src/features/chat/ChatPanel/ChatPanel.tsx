import { useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import type { Artifact } from "@/services/artifact/types";
import { chatService } from "@/services/chat/ChatService";
import { ChatInput } from "../ChatInput";
import { type ChatMessageItem, ChatMessageList } from "../ChatMessageList";
import {
	CHAT_EMPTY_ANSWER_MESSAGE,
	CHAT_ERROR_MESSAGE,
	CHAT_PANEL_TITLE,
	CHAT_STREAM_EMPTY_MESSAGE,
} from "../chatLabels";
import type { CitationClickDetails } from "../citationNavigation";
import styles from "./ChatPanel.module.css";

interface ChatPanelProps {
	contentId: string;
	artifacts: Artifact[];
	onCitationClick?: (details: CitationClickDetails) => void;
}

function createMessageId(): string {
	return `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
}

export function ChatPanel({
	contentId,
	artifacts,
	onCitationClick,
}: ChatPanelProps) {
	const [messages, setMessages] = useState<ChatMessageItem[]>([]);
	const [streaming, setStreaming] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [question, setQuestion] = useState("");

	const artifactTypesById = useMemo(
		() =>
			Object.fromEntries(
				artifacts.map((artifact) => [artifact.id, artifact.type]),
			),
		[artifacts],
	);

	function removeAssistantMessage(assistantMessageId: string): void {
		setMessages((currentMessages) =>
			currentMessages.filter((message) => message.id !== assistantMessageId),
		);
	}

	function handleSubmit(): void {
		const trimmedQuestion = question.trim();

		if (trimmedQuestion === "" || streaming) {
			return;
		}

		const userMessage: ChatMessageItem = {
			id: createMessageId(),
			role: "user",
			content: trimmedQuestion,
		};
		const assistantMessageId = createMessageId();

		setMessages((currentMessages) => [
			...currentMessages,
			userMessage,
			{
				id: assistantMessageId,
				role: "assistant",
				content: "",
				streaming: true,
			},
		]);
		setQuestion("");
		setStreaming(true);
		setError(null);

		let receivedToken = false;

		void chatService
			.streamQuestion(contentId, trimmedQuestion, {
				onToken: (token) => {
					receivedToken = true;
					setMessages((currentMessages) =>
						currentMessages.map((message) =>
							message.id === assistantMessageId
								? {
										...message,
										content: message.content + token.text,
									}
								: message,
						),
					);
				},
				onDone: () => {
					setStreaming(false);
					setMessages((currentMessages) => {
						const assistantMessage = currentMessages.find(
							(message) => message.id === assistantMessageId,
						);

						if (assistantMessage === undefined) {
							return currentMessages;
						}

						if (assistantMessage.content.trim() === "") {
							setError(CHAT_EMPTY_ANSWER_MESSAGE);
							return currentMessages.filter(
								(message) => message.id !== assistantMessageId,
							);
						}

						return currentMessages.map((message) =>
							message.id === assistantMessageId
								? { ...message, streaming: false }
								: message,
						);
					});
				},
				onError: () => {
					setStreaming(false);

					if (!receivedToken) {
						removeAssistantMessage(assistantMessageId);
						setError(CHAT_STREAM_EMPTY_MESSAGE);
						return;
					}

					setMessages((currentMessages) =>
						currentMessages.map((message) =>
							message.id === assistantMessageId
								? {
										...message,
										streaming: false,
										failed: true,
									}
								: message,
						),
					);
					setError(CHAT_ERROR_MESSAGE);
				},
			})
			.catch(() => {
				setStreaming(false);

				if (!receivedToken) {
					removeAssistantMessage(assistantMessageId);
					setError(CHAT_STREAM_EMPTY_MESSAGE);
					return;
				}

				setMessages((currentMessages) =>
					currentMessages.map((message) =>
						message.id === assistantMessageId
							? {
									...message,
									streaming: false,
									failed: true,
								}
							: message,
					),
				);
				setError(CHAT_ERROR_MESSAGE);
			});
	}

	return (
		<Card className={styles.chatPanel}>
			<p className={styles.label}>{CHAT_PANEL_TITLE}</p>
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
				loading={streaming}
			/>
		</Card>
	);
}
