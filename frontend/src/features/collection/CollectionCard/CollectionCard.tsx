import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import type { Collection } from "@/services/collection/types";
import styles from "./CollectionCard.module.css";

interface CollectionCardProps {
	collection: Collection;
}

export function CollectionCard({ collection }: CollectionCardProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<h3 className={styles.title}>{collection.name}</h3>
				<span className={styles.itemCount}>
					{t("workspace.collections.itemCount")}
				</span>
			</div>
			<p className={styles.description}>{collection.description}</p>
		</Card>
	);
}
