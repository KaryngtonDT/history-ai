import type { LibraryContent } from "@/services/library/types";
import { LibraryContentCard } from "../LibraryContentCard";
import styles from "./LibraryContentList.module.css";

interface LibraryContentListProps {
	contents: LibraryContent[];
}

export function LibraryContentList({ contents }: LibraryContentListProps) {
	return (
		<ul className={styles.list}>
			{contents.map((content) => (
				<li key={content.id} className={styles.item}>
					<LibraryContentCard content={content} />
				</li>
			))}
		</ul>
	);
}
