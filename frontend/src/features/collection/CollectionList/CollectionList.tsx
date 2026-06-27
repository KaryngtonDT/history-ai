import type { Collection } from "@/services/collection/types";
import { CollectionCard } from "../CollectionCard";
import styles from "./CollectionList.module.css";

interface CollectionListProps {
	collections: Collection[];
}

export function CollectionList({ collections }: CollectionListProps) {
	return (
		<ul className={styles.list}>
			{collections.map((collection) => (
				<li key={collection.id} className={styles.item}>
					<CollectionCard collection={collection} />
				</li>
			))}
		</ul>
	);
}
