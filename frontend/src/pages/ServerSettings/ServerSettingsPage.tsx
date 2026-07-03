import { PageIntroduction } from "@/features/product";
import { ServerDashboard } from "@/features/server/ServerDashboard";
import { useTranslation } from "@/i18n";

export function ServerSettingsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow={t("mobile.title")}
				title={t("server.title")}
				description={t("server.description")}
				whatCanIDo={t("server.whatCanIDo")}
			/>
			<ServerDashboard />
		</section>
	);
}
