import styles from "./ShadowChallengePrompt.module.css";

interface ShadowChallengePromptProps {
	questionText?: string;
	explanation?: string;
}

export function ShadowChallengePrompt({
	questionText,
	explanation,
}: ShadowChallengePromptProps) {
	if (!questionText && !explanation) {
		return null;
	}

	return (
		<div>
			{questionText ? <p className={styles.prompt}>{questionText}</p> : null}
			{explanation ? <p className={styles.explanation}>{explanation}</p> : null}
		</div>
	);
}
