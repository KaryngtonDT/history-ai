import { Link } from "react-router";
import { ExplainThisButton } from "@/features/help";
import { PageIntroduction } from "@/features/product";
import { RuntimeCenter } from "@/features/runtime/RuntimeCenter";
import { RuntimeHealthDashboard } from "@/features/runtime/RuntimeHealthDashboard";
import { useTranslation } from "@/i18n/useTranslation";

export function RuntimeSettingsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow="Settings"
				title="AI Runtime"
				description="Runtime health dashboard, hardware profile, engine recommendations, and operational console."
				whatCanIDo="Review platform health, understand blocked engines, and run validate, benchmark, and provision actions."
				secondaryActions={<ExplainThisButton featureId="ai-engines" />}
			/>
			<p>
				<Link to="/settings/runtime/analytics">
					{t("settings.runtime.analytics.open")}
				</Link>
			</p>
			<RuntimeHealthDashboard />
			<h2 style={{ marginTop: "2rem" }}>Engine Console</h2>
			<RuntimeCenter />
		</section>
	);
}
