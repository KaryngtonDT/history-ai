import { AIEngineSettings } from "@/features/ai/AIEngineSettings";
import { ExplainThisButton } from "@/features/help";
import { PageIntroduction } from "@/features/product";
import styles from "./AIEngineSettingsPage.module.css";

export function AIEngineSettingsPage() {
	return (
		<section className={styles.root}>
			<PageIntroduction
				eyebrow="Settings"
				title="AI Engines"
				description="View registered providers and what each engine can do."
				whatCanIDo="Review available speech, translation, TTS, voice clone, and lip-sync providers before configuring your pipeline."
				secondaryActions={<ExplainThisButton featureId="ai-engines" />}
			/>
			<AIEngineSettings />
		</section>
	);
}
