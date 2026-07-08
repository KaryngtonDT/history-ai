import { Link } from "react-router";
import { PageIntroduction } from "@/features/product";
import { RuntimeAnalyticsDashboard } from "@/features/runtime/RuntimeAnalyticsDashboard";
import { useTranslation } from "@/i18n/useTranslation";

export function RuntimeAnalyticsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow={t("settings.eyebrow")}
				title={t("settings.runtime.analytics.title")}
				description={t("settings.runtime.analytics.description")}
				whatCanIDo={t("settings.runtime.analytics.whatCanIDo")}
				secondaryActions={
					<Link to="/settings/runtime">
						{t("settings.runtime.analytics.back")}
					</Link>
				}
			/>
			<RuntimeAnalyticsDashboard />
		</section>
	);
}
