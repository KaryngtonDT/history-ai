import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import type { LibraryItem } from "@/services/library/types";
import styles from "./LibraryContentCard.module.css";

interface LibraryContentCardProps {
	item: LibraryItem;
	onAssignClick: () => void;
}

function typeLabel(type: LibraryItem["type"]): string {
	return `workspace.library.typeLabels.${type}`;
}

export function LibraryContentCard({
	item,
	onAssignClick,
}: LibraryContentCardProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<Link to={`/library/${item.id}`} className={styles.titleLink}>
					<h3 className={styles.title}>{item.title}</h3>
				</Link>
				<div className={styles.badges}>
					<Badge variant="neutral">{t(typeLabel(item.type))}</Badge>
				</div>
			</div>
			<div className={styles.actions}>
				<Button
					type="button"
					variant="secondary"
					size="sm"
					onClick={onAssignClick}
				>
					{t("workspace.library.addToCollection")}
				</Button>
			</div>
		</Card>
	);
}
