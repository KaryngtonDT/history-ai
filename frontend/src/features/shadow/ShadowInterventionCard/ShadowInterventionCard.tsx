import { useEffect, useRef } from "react";
import { useTranslation } from "@/i18n/useTranslation";
import type { ShadowIntervention } from "@/services/shadow/types";
import { ShadowAnswerInput } from "../ShadowAnswerInput";
import { ShadowChallengePrompt } from "../ShadowChallengePrompt";
import { speakShadowAnswer } from "../ShadowVoiceButton";
import { ShadowWhyInterrupted } from "../ShadowWhyInterrupted";
import styles from "./ShadowInterventionCard.module.css";

interface ShadowInterventionCardProps {
	intervention: ShadowIntervention;
	answer: string;
	reply: string | null;
	isBusy: boolean;
	onAnswerChange: (value: string) => void;
	onSubmitAnswer: () => void;
	onSkip: () => void;
}

export function ShadowInterventionCard({
	intervention,
	answer,
	reply,
	isBusy,
	onAnswerChange,
	onSubmitAnswer,
	onSkip,
}: ShadowInterventionCardProps) {
	const { t } = useTranslation();
	const spokenRef = useRef<string | null>(null);

	useEffect(() => {
		const prompt =
			intervention.challenge?.questionText ?? intervention.explanation ?? "";

		if (prompt && spokenRef.current !== intervention.id) {
			spokenRef.current = intervention.id;
			speakShadowAnswer(prompt);
		}
	}, [intervention]);

	return (
		<section
			className={styles.card}
			aria-live="polite"
			aria-label={t("pipeline.shadow.interventionTitle")}
		>
			<div className={styles.header}>
				<h2 className={styles.title}>
					{t("pipeline.shadow.interventionTitle")}
				</h2>
			</div>

			<ShadowWhyInterrupted reason={intervention.reason} />
			<ShadowChallengePrompt
				questionText={intervention.challenge?.questionText}
				explanation={intervention.explanation}
			/>

			{reply ? <p className={styles.reply}>{reply}</p> : null}

			{isBusy ? (
				<p className={styles.busy}>
					{t("pipeline.shadow.interventionThinking")}
				</p>
			) : null}

			{!reply ? (
				<ShadowAnswerInput
					value={answer}
					disabled={isBusy}
					onChange={onAnswerChange}
					onSubmit={onSubmitAnswer}
					onSkip={onSkip}
				/>
			) : null}
		</section>
	);
}
