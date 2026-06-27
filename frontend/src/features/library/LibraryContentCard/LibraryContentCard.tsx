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
	const handleClick = () => {
		console.log(`/content/${item.contentId}`);
	};

	return (
		<Card
			className={styles.card}
			role="button"
			tabIndex={0}
			onClick={handleClick}
			onKeyDown={(event) => {
				if (event.key === "Enter" || event.key === " ") {
					event.preventDefault();
					handleClick();
				}
			}}
		>
			<div className={styles.header}>
				<h3 className={styles.title}>{item.title}</h3>
				<div className={styles.badges}>
					<Badge variant="neutral">{typeLabel(item.type)}</Badge>
				</div>
			</div>
		</Card>
	);
}
