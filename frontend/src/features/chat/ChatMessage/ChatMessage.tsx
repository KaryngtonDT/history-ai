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
	onCitationClick,
}: ChatMessageProps) {
	const isUser = speaker === "user";

	return (
		<article
			className={`${styles.message} ${isUser ? styles.userMessage : styles.assistantMessage}`}
			aria-label={isUser ? CHAT_USER_PREFIX : CHAT_ASSISTANT_PREFIX}
		>
			<p className={styles.prefix}>{isUser ? "👤" : "🤖"}</p>
			<p className={styles.content}>
				{isUser
					? content
					: renderAssistantContent(content, citations, onCitationClick)}
			</p>
		</article>
	);
}
