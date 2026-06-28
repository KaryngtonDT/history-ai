import { CHAT_ASSISTANT_PREFIX, CHAT_USER_PREFIX } from "../chatLabels";
import styles from "./ChatMessage.module.css";

export interface ChatMessageProps {
	speaker: "user" | "assistant";
	content: string;
}

export function ChatMessage({ speaker, content }: ChatMessageProps) {
	const isUser = speaker === "user";

	return (
		<article
			className={`${styles.message} ${isUser ? styles.userMessage : styles.assistantMessage}`}
			aria-label={isUser ? CHAT_USER_PREFIX : CHAT_ASSISTANT_PREFIX}
		>
			<p className={styles.prefix}>{isUser ? "👤" : "🤖"}</p>
			<p className={styles.content}>{content}</p>
		</article>
	);
}
