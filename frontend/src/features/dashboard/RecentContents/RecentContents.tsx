import { Card } from "@/components/ui/Card";
import styles from "./RecentContents.module.css";

export function RecentContents() {
	return (
		<section className={styles.root} aria-labelledby="recent-contents-heading">
			<h3 id="recent-contents-heading" className={styles.heading}>
				Recent Content
			</h3>
			<Card>
				<p className={styles.placeholder}>Loading...</p>
			</Card>
		</section>
	);
}
