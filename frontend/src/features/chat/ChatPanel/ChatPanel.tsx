import { useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import type { Artifact } from "@/services/artifact/types";
import { chatService } from "@/services/chat/ChatService";
import { ChatInput } from "../ChatInput";
import { type ChatMessageItem, ChatMessageList } from "../ChatMessageList";
import {
	CHAT_EMPTY_ANSWER_MESSAGE,
	CHAT_ERROR_MESSAGE,
	CHAT_LOADING_LABEL,
	CHAT_PANEL_TITLE,
} from "../chatLabels";
import styles from "./ChatPanel.module.css";

interface ChatPanelProps {
	contentId: string;
	artifacts: Artifact[];
}

function createMessageId(): string {
	return `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
}

export function ChatPanel({ contentId, artifacts }: ChatPanelProps) {
	const [messages, setMessages] = useState<ChatMessageItem[]>([]);
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [question, setQuestion] = useState("");

	const artifactTypesById = useMemo(
		() =>
			Object.fromEntries(
				artifacts.map((artifact) => [artifact.id, artifact.type]),
			),
		[artifacts],
	);

	async function handleSubmit(): Promise<void> {
		const trimmedQuestion = question.trim();

		if (trimmedQuestion === "" || loading) {
			return;
		}

		const userMessage: ChatMessageItem = {
			id: createMessageId(),
			role: "user",
			content: trimmedQuestion,
		};

		setMessages((currentMessages) => [...currentMessages, userMessage]);
		setQuestion("");
		setLoading(true);
		setError(null);

		try {
			const result = await chatService.askQuestion(contentId, trimmedQuestion);

			if (result.answer.trim() === "") {
				setError(CHAT_EMPTY_ANSWER_MESSAGE);
				return;
			}

			setMessages((currentMessages) => [
				...currentMessages,
				{
					id: createMessageId(),
					role: "assistant",
					content: result.answer,
					sources: result.sources,
					citations: result.citations,
				},
			]);
		} catch {
			setError(CHAT_ERROR_MESSAGE);
		} finally {
			setLoading(false);
		}
	}

	return (
		<Card className={styles.chatPanel}>
			<p className={styles.label}>{CHAT_PANEL_TITLE}</p>
			<ChatMessageList
				messages={messages}
				artifactTypesById={artifactTypesById}
			/>
			{loading ? (
				<div className={styles.loadingState}>
					<Spinner label={CHAT_LOADING_LABEL} />
				</div>
			) : null}
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
				onSubmit={() => {
					void handleSubmit();
				}}
				loading={loading}
			/>
		</Card>
	);
}
