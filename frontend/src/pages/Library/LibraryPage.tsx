import { EmptyState } from "@/components/ui/EmptyState";
import styles from "./LibraryPage.module.css";

export function LibraryPage() {
	return (
		<section>
			<h2 className={styles.title}>Library</h2>
			<div className={styles.content}>
				<EmptyState
					title="No content yet"
					description="Import your first PDF."
				/>
			</div>
		</section>
	);
}
