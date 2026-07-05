import { ExplainThisButton } from "@/features/help";
import { RuntimeCenter } from "@/features/runtime/RuntimeCenter";
import { PageIntroduction } from "@/features/product";

export function RuntimeSettingsPage() {
	return (
		<section>
			<PageIntroduction
				eyebrow="Settings"
				title="AI Runtime"
				description="Discover, verify, benchmark, and validate the engines that actually run your pipeline."
				whatCanIDo="Check readiness, run engine tests, validate the full pipeline, and review recommendations."
				secondaryActions={<ExplainThisButton featureId="runtime" />}
			/>
			<RuntimeCenter />
		</section>
	);
}
