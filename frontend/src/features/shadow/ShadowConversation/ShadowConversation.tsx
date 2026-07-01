import { useTranslation } from "@/i18n/useTranslation";
import type { ShadowInteraction } from "@/services/shadow/types";
import { ShadowTimeline } from "../ShadowTimeline";
import styles from "./ShadowConversation.module.css";

interface ShadowConversationProps {
	interactions: ShadowInteraction[];
	question: string;
	isAsking: boolean;
	onQuestionChange: (value: string) => void;
	onSubmit: () => void;
}

export function ShadowConversation({
	interactions,
	question,
	isAsking,
	onQuestionChange,
	onSubmit,
}: ShadowConversationProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.panel}>
			<h2 className={styles.title}>{t("pipeline.shadow.conversationTitle")}</h2>
			<ShadowTimeline interactions={interactions} />
			<form
				className={styles.form}
				onSubmit={(event) => {
					event.preventDefault();
					onSubmit();
				}}
			>
				<label className={styles.label} htmlFor="shadow-question">
					{t("pipeline.shadow.questionLabel")}
				</label>
				<textarea
					id="shadow-question"
					className={styles.input}
					value={question}
					rows={3}
					onChange={(event) => onQuestionChange(event.target.value)}
					placeholder={t("pipeline.shadow.questionPlaceholder")}
				/>
				<button type="submit" className={styles.submit} disabled={isAsking}>
					{isAsking
						? t("pipeline.shadow.asking")
						: t("pipeline.shadow.askShadow")}
				</button>
			</form>
		</section>
	);
}
