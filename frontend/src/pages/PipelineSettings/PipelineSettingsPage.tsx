import { ExplainThisButton } from "@/features/help";
import { PipelineBuilder } from "@/features/pipeline";
import { PageIntroduction } from "@/features/product";

export function PipelineSettingsPage() {
	return (
		<section>
			<PageIntroduction
				eyebrow="Settings"
				title="Pipeline Configuration"
				description="Assign AI engines to each processing stage."
				whatCanIDo="Map providers to transcript, translation, audio, voice clone, lip sync, and render steps. Changes apply on the next run."
				secondaryActions={<ExplainThisButton featureId="pipeline" />}
			/>
			<PipelineBuilder />
		</section>
	);
}
