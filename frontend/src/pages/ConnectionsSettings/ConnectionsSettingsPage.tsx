import { ConnectionsCenter } from "@/features/connections/ConnectionsCenter";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n";

export function ConnectionsSettingsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow={t("mobile.title")}
				title={t("connections.title")}
				description={t("connections.description")}
				whatCanIDo={t("connections.whatCanIDo")}
			/>
			<ConnectionsCenter />
		</section>
	);
}
