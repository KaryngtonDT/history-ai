import type { Content } from "@/services/content/types";
import { RecentContentCard } from "./RecentContentCard";
import styles from "./RecentContents.module.css";

interface RecentContentsProps {
	contents: Content[];
}

export function RecentContents({ contents }: RecentContentsProps) {
	return (
		<section className={styles.root} aria-labelledby="recent-contents-heading">
			<h3 id="recent-contents-heading" className={styles.heading}>
				Recent Content
			</h3>
			<div className={styles.list}>
				{contents.map((content) => (
					<RecentContentCard key={content.id} content={content} />
				))}
			</div>
		</section>
	);
}
