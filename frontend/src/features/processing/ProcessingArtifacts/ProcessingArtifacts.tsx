import { useEffect, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { ProcessingFlashcards } from "@/features/processing/ProcessingFlashcards";
import { ProcessingQuiz } from "@/features/processing/ProcessingQuiz";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingArtifacts.module.css";

interface ProcessingArtifactsProps {
	contentId: string;
}

interface ArtifactCardProps {
	label: string;
	content: string;
	scrollable?: boolean;
}

function ArtifactCard({
	label,
	content,
	scrollable = false,
}: ArtifactCardProps) {
	return (
		<Card className={styles.card}>
			<p className={styles.label}>{label}</p>
			<p className={scrollable ? styles.transcriptContent : styles.content}>
				{content}
			</p>
		</Card>
	);
}

function findArtifactByType(
	artifacts: Artifact[],
	type: Artifact["type"],
): Artifact | null {
	return artifacts.find((artifact) => artifact.type === type) ?? null;
}

export function ProcessingArtifacts({ contentId }: ProcessingArtifactsProps) {
	const [summary, setSummary] = useState<Artifact | null>(null);
	const [transcript, setTranscript] = useState<Artifact | null>(null);
	const [quiz, setQuiz] = useState<Artifact | null>(null);
	const [flashcards, setFlashcards] = useState<Artifact | null>(null);
	const [loading, setLoading] = useState(true);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [isEmpty, setIsEmpty] = useState(false);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setLoadError(null);
		setIsEmpty(false);
		setSummary(null);
		setTranscript(null);
		setQuiz(null);
		setFlashcards(null);

		void artifactService
			.listByContentId(contentId)
			.then((artifacts) => {
				if (cancelled) {
					return;
				}

				const summaryArtifact = findArtifactByType(artifacts, "summary");
				const transcriptArtifact = findArtifactByType(artifacts, "transcript");
				const quizArtifact = findArtifactByType(artifacts, "quiz");
				const flashcardsArtifact = findArtifactByType(artifacts, "flashcards");

				if (
					!summaryArtifact &&
					!transcriptArtifact &&
					!quizArtifact &&
					!flashcardsArtifact
				) {
					setIsEmpty(true);
					setSummary(null);
					setTranscript(null);
					setQuiz(null);
					setFlashcards(null);
					return;
				}

				setSummary(summaryArtifact);
				setTranscript(transcriptArtifact);
				setQuiz(quizArtifact);
				setFlashcards(flashcardsArtifact);
				setIsEmpty(false);
			})
			.catch(() => {
				if (!cancelled) {
					setLoadError(
						"Could not load artifacts. Check that the backend is running.",
					);
					setSummary(null);
					setTranscript(null);
					setQuiz(null);
					setFlashcards(null);
					setIsEmpty(false);
				}
			})
			.finally(() => {
				if (!cancelled) {
					setLoading(false);
				}
			});

		return () => {
			cancelled = true;
		};
	}, [contentId]);

	if (loading) {
		return (
			<div className={styles.loading}>
				<Spinner label="Loading artifacts" />
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState title="Unable to load artifacts" description={loadError} />
		);
	}

	if (isEmpty) {
		return (
			<EmptyState
				title="No artifacts yet"
				description="Generated learning artifacts will appear here once processing output is available."
			/>
		);
	}

	return (
		<div className={styles.list}>
			{summary ? (
				<ArtifactCard label="Summary" content={summary.content} />
			) : null}
			{transcript ? (
				<ArtifactCard
					label="Transcript"
					content={transcript.content}
					scrollable
				/>
			) : null}
			<ProcessingQuiz artifact={quiz} />
			<ProcessingFlashcards artifact={flashcards} />
		</div>
	);
}
