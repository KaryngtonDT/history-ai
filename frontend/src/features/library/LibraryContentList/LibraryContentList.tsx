import type { Content } from "@/services/content/domain/Content";
import { LibraryContentCard } from "../LibraryContentCard";
import styles from "./LibraryContentList.module.css";

interface LibraryContentListProps {
	contents: Content[];
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
