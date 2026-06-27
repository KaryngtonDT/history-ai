import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import type { LibraryItem } from "@/services/library/types";
import styles from "./LibraryContentCard.module.css";

interface LibraryContentCardProps {
	item: LibraryItem;
}

function typeLabel(type: LibraryItem["type"]): string {
	const labels: Record<LibraryItem["type"], string> = {
		summary: "Summary",
		quiz: "Quiz",
		flashcards: "Flashcards",
		transcript: "Transcript",
		timeline: "Timeline",
		podcast: "Podcast",
	};

	return labels[type];
}

export function LibraryContentCard({ item }: LibraryContentCardProps) {
	return (
		<Link to={`/library/${item.id}`} className={styles.link}>
			<Card className={styles.card}>
				<div className={styles.header}>
					<h3 className={styles.title}>{item.title}</h3>
					<div className={styles.badges}>
						<Badge variant="neutral">{typeLabel(item.type)}</Badge>
					</div>
				</div>
			</Card>
		</Link>
	);
}
