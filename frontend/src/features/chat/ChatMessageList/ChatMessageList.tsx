import type { ArtifactType } from "@/services/artifact/types";
import type { ChatCitation, ChatSource } from "@/services/chat/types";
import { ChatMessage } from "../ChatMessage";
import type { CitationClickDetails } from "../citationNavigation";
import { SourcesPanel } from "../SourcesPanel";
import styles from "./ChatMessageList.module.css";

export interface ChatMessageItem {
	id: string;
	role: "user" | "assistant";
	content: string;
	sources?: ChatSource[];
	citations?: ChatCitation[];
	streaming?: boolean;
	failed?: boolean;
}

export interface ChatMessageListProps {
	messages: ChatMessageItem[];
	artifactTypesById: Record<string, ArtifactType>;
	onCitationClick?: (details: CitationClickDetails) => void;
}

export function ChatMessageList({
	messages,
	artifactTypesById,
	onCitationClick,
}: ChatMessageListProps) {
	if (messages.length === 0) {
		return null;
	}

	return (
		<div className={styles.messageList}>
			{messages.map((message) => (
				<div key={message.id} className={styles.messageBlock}>
					<ChatMessage
						speaker={message.role}
						content={message.content}
						citations={message.citations}
						streaming={message.streaming}
						failed={message.failed}
						onCitationClick={
							message.role === "assistant" ? onCitationClick : undefined
						}
					/>
					{message.role === "assistant" &&
					message.sources !== undefined &&
					message.sources.length > 0 ? (
						<SourcesPanel
							sources={message.sources}
							citations={message.citations}
							artifactTypesById={artifactTypesById}
							onCitationClick={onCitationClick}
						/>
					) : null}
				</div>
			))}
		</div>
	);
}
