import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import styles from "./SettingsPage.module.css";

export function SettingsPage() {
	return (
		<section>
			<h2 className={styles.title}>Settings</h2>
			<p className={styles.description}>Coming soon</p>
			<div className={styles.preview}>
				<div>
					<p className={styles.previewLabel}>Processing preview</p>
					<Progress value={0} />
				</div>
				<div className={styles.spinnerRow}>
					<Spinner />
					<span className={styles.spinnerLabel}>Loading indicator</span>
				</div>
			</div>
		</section>
	);
}
