import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { ArtifactCardHeader } from "@/features/processing/artifactRenderers/ArtifactCardHeader";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingQuiz.module.css";

interface ProcessingQuizProps {
	artifact: Artifact | null;
	contentId?: string;
}

function renderQuizLine(line: string, index: number) {
	if (line.startsWith("# ")) {
		const heading = line.slice(2).trim();
		if (heading.toLowerCase() === "quiz") {
			return null;
		}

		return (
			<h2 key={index} className={styles.title}>
				{heading}
			</h2>
		);
	}

	if (line.startsWith("## ")) {
		return (
			<h3 key={index} className={styles.questionHeading}>
				{line.slice(3)}
			</h3>
		);
	}

	if (line.startsWith("- ")) {
		return (
			<p key={index} className={styles.option}>
				{line}
			</p>
		);
	}

	if (line.startsWith("Answer:")) {
		return (
			<p key={index} className={styles.answer}>
				{line}
			</p>
		);
	}

	if (line.trim() === "") {
		return null;
	}

	return (
		<p key={index} className={styles.line}>
			{line}
		</p>
	);
}

function QuizContent({ content }: { content: string }) {
	return (
		<div className={styles.quizContent}>
			{content.split("\n").map((line, index) => renderQuizLine(line, index))}
		</div>
	);
}

export function ProcessingQuiz({ artifact, contentId }: ProcessingQuizProps) {
	if (artifact === null) {
		return (
			<Card className={styles.card}>
				<p className={styles.label}>Quiz</p>
				<EmptyState
					className={styles.emptyState}
					title="No quiz yet"
					description="A quiz will appear here once processing generates quiz artifacts."
				/>
			</Card>
		);
	}

	return (
		<Card className={styles.card}>
			{contentId ? (
				<ArtifactCardHeader
					label="Quiz"
					artifact={artifact}
					contentId={contentId}
				/>
			) : (
				<p className={styles.label}>Quiz</p>
			)}
			<QuizContent content={artifact.content} />
		</Card>
	);
}
