import { AIEngineSettings } from "@/features/ai/AIEngineSettings";
import styles from "./AIEngineSettingsPage.module.css";

export function AIEngineSettingsPage() {
	return (
		<section className={styles.root}>
			<AIEngineSettings />
		</section>
	);
}
