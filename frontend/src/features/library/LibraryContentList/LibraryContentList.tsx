import type { LibraryItem } from "@/services/library/types";
import { LibraryContentCard } from "../LibraryContentCard";
import styles from "./LibraryContentList.module.css";

interface LibraryContentListProps {
	items: LibraryItem[];
}

export function LibraryContentList({ items }: LibraryContentListProps) {
	return (
		<ul className={styles.list}>
			{items.map((item) => (
				<li key={item.id} className={styles.item}>
					<LibraryContentCard item={item} />
				</li>
			))}
		</ul>
	);
}
