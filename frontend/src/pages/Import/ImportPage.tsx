import { Button } from "@/components/ui/Button";
import styles from "./ImportPage.module.css";

export function ImportPage() {
	return (
		<section>
			<h2 className={styles.title}>Import</h2>
			<p className={styles.description}>
				Bring knowledge sources into History AI.
			</p>
			<div className={styles.action}>
				<Button variant="primary">Import PDF</Button>
			</div>
		</section>
	);
}
