import { ExplainThisButton } from "@/features/help";
import { PipelineBuilder } from "@/features/pipeline";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n";

export function PipelineSettingsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow={t("workspace.settings.pipeline.eyebrow")}
				title={t("workspace.settings.pipeline.title")}
				description={t("workspace.settings.pipeline.description")}
				whatCanIDo={t("workspace.settings.pipeline.whatCanIDo")}
				secondaryActions={<ExplainThisButton featureId="pipeline" />}
			/>
			<PipelineBuilder />
		</section>
	);
}
