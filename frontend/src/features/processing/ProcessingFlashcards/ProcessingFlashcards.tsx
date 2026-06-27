import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { ArtifactCardHeader } from "@/features/processing/artifactRenderers/ArtifactCardHeader";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingFlashcards.module.css";

interface ProcessingFlashcardsProps {
	artifact: Artifact | null;
	contentId?: string;
}

interface ParsedFlashcard {
	title: string;
	front: string;
	back: string;
}

function parseFlashcards(content: string): ParsedFlashcard[] {
	const cards: ParsedFlashcard[] = [];
	let current: {
		title: string;
		frontLines: string[];
		backLines: string[];
		section: "none" | "front" | "back";
	} | null = null;

	const pushCurrent = () => {
		if (current === null) {
			return;
		}

		cards.push({
			title: current.title,
			front: current.frontLines.join("\n").trim(),
			back: current.backLines.join("\n").trim(),
		});
	};

	for (const line of content.split("\n")) {
		if (line.startsWith("# ") && !line.startsWith("## ")) {
			continue;
		}

		if (line.startsWith("## ")) {
			pushCurrent();
			current = {
				title: line.slice(3).trim(),
				frontLines: [],
				backLines: [],
				section: "none",
			};
			continue;
		}

		if (line.trim() === "---" || current === null) {
			continue;
		}

		if (line.trim() === "Front:") {
			current.section = "front";
			continue;
		}

		if (line.trim() === "Back:") {
			current.section = "back";
			continue;
		}

		if (current.section === "front") {
			current.frontLines.push(line);
		} else if (current.section === "back") {
			current.backLines.push(line);
		}
	}

	pushCurrent();

	return cards;
}

function FlashcardItem({ card }: { card: ParsedFlashcard }) {
	return (
		<article className={styles.flashcardItem}>
			<h3 className={styles.cardTitle}>{card.title}</h3>
			<div className={styles.side}>
				<p className={styles.sideLabel}>Front:</p>
				<p className={styles.sideContent}>{card.front}</p>
			</div>
			<div className={styles.side}>
				<p className={styles.sideLabel}>Back:</p>
				<p className={styles.sideContent}>{card.back}</p>
			</div>
		</article>
	);
}

function FlashcardsContent({ content }: { content: string }) {
	const cards = parseFlashcards(content);

	if (cards.length === 0) {
		return <p className={styles.fallbackContent}>{content}</p>;
	}

	return (
		<div className={styles.flashcardsContent}>
			{cards.map((card) => (
				<FlashcardItem key={card.title} card={card} />
			))}
		</div>
	);
}

export function ProcessingFlashcards({
	artifact,
	contentId,
}: ProcessingFlashcardsProps) {
	if (artifact === null) {
		return (
			<Card className={styles.card}>
				<p className={styles.label}>Flashcards</p>
				<EmptyState
					className={styles.emptyState}
					title="No flashcards yet"
					description="Flashcards will appear here once processing generates flashcard artifacts."
				/>
			</Card>
		);
	}

	return (
		<Card className={styles.card}>
			{contentId ? (
				<ArtifactCardHeader
					label="Flashcards"
					artifact={artifact}
					contentId={contentId}
				/>
			) : (
				<p className={styles.label}>Flashcards</p>
			)}
			<FlashcardsContent content={artifact.content} />
		</Card>
	);
}
