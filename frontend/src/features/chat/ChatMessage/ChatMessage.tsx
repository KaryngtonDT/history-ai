import type { ReactNode } from "react";
import { CHAT_ASSISTANT_PREFIX, CHAT_USER_PREFIX } from "../chatLabels";
import {
	type ChatCitationRef,
	type CitationClickDetails,
	resolveCitationClickDetails,
} from "../citationNavigation";
import styles from "./ChatMessage.module.css";

export interface ChatMessageProps {
	speaker: "user" | "assistant";
	content: string;
	citations?: ChatCitationRef[];
	streaming?: boolean;
	failed?: boolean;
	onCitationClick?: (details: CitationClickDetails) => void;
}

function renderAssistantContent(
	content: string,
	citations: ChatCitationRef[] | undefined,
	onCitationClick: ((details: CitationClickDetails) => void) | undefined,
): ReactNode {
	if (
		onCitationClick === undefined ||
		citations === undefined ||
		citations.length === 0
	) {
		return content;
	}

	const parts: ReactNode[] = [];
	let lastIndex = 0;
	const pattern = /\[(\d+)\]/g;

	for (const match of content.matchAll(pattern)) {
		const matchedText = match[0];
		const matchIndex = match.index;

		if (matchedText === undefined || matchIndex === undefined) {
			continue;
		}

		if (matchIndex > lastIndex) {
			parts.push(content.slice(lastIndex, matchIndex));
		}

		const number = Number.parseInt(match[1] ?? "", 10);
		const citationDetails = resolveCitationClickDetails(citations, number);

		if (citationDetails !== undefined) {
			parts.push(
				<button
					key={`${matchIndex}-${number}`}
					type="button"
					className={styles.citationMarker}
					aria-label={`Citation ${number}`}
					onClick={() => {
						onCitationClick(citationDetails);
					}}
				>
					{matchedText}
				</button>,
			);
		} else {
			parts.push(matchedText);
		}

		lastIndex = matchIndex + matchedText.length;
	}

	if (lastIndex < content.length) {
		parts.push(content.slice(lastIndex));
	}

	return parts.length > 0 ? parts : content;
}

export function ChatMessage({
	speaker,
	content,
	citations,
	streaming = false,
	failed = false,
	onCitationClick,
}: ChatMessageProps) {
	const isUser = speaker === "user";
	const assistantClasses = [
		styles.message,
		styles.assistantMessage,
		streaming ? styles.streamingMessage : "",
		failed ? styles.failedMessage : "",
	]
		.filter(Boolean)
		.join(" ");

	return (
		<article
			className={
				isUser ? `${styles.message} ${styles.userMessage}` : assistantClasses
			}
			aria-label={isUser ? CHAT_USER_PREFIX : CHAT_ASSISTANT_PREFIX}
			aria-busy={streaming || undefined}
		>
			<p className={styles.prefix}>{isUser ? "👤" : "🤖"}</p>
			<p className={styles.content}>
				{isUser ? (
					content
				) : (
					<>
						{streaming && content === "" ? (
							<span className={styles.generating}>Generating...</span>
						) : null}
						{content !== ""
							? renderAssistantContent(content, citations, onCitationClick)
							: null}
						{failed ? (
							<span className={styles.failed}>Unable to generate answer.</span>
						) : null}
					</>
				)}
			</p>
		</article>
	);
}
