import { AIEngineSettings } from "@/features/ai/AIEngineSettings";
import { ExplainThisButton } from "@/features/help";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n";
import styles from "./AIEngineSettingsPage.module.css";

export function AIEngineSettingsPage() {
	const { t } = useTranslation();

	return (
		<section className={styles.root}>
			<PageIntroduction
				eyebrow={t("workspace.settings.aiEngine.eyebrow")}
				title={t("workspace.settings.aiEngine.title")}
				description={t("workspace.settings.aiEngine.description")}
				whatCanIDo={t("workspace.settings.aiEngine.whatCanIDo")}
				secondaryActions={<ExplainThisButton featureId="ai-engines" />}
			/>
			<AIEngineSettings />
		</section>
	);
}
