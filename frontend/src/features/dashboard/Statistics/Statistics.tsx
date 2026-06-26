import { Card } from "@/components/ui/Card";
import styles from "./Statistics.module.css";

export function Statistics() {
	return (
		<section className={styles.root} aria-labelledby="statistics-heading">
			<h3 id="statistics-heading" className={styles.heading}>
				Statistics
			</h3>
			<Card>
				<p className={styles.placeholder}>Loading...</p>
			</Card>
		</section>
	);
}
