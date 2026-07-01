import { Link } from "react-router";
import { PageIntroduction } from "@/features/product";
import { LanguageSettings } from "@/features/settings/LanguageSettings";
import { useTranslation } from "@/i18n";
import styles from "./SettingsPage.module.css";

const SETTINGS_LINKS = [
	{ to: "/settings/ai", key: "aiEngines" },
	{ to: "/settings/pipeline", key: "pipeline" },
] as const;

export function SettingsPage() {
	const { t } = useTranslation();

	return (
		<section>
			<PageIntroduction
				eyebrow={t("settings.eyebrow")}
				title={t("settings.title")}
				description={t("settings.description")}
				whatCanIDo={t("settings.whatCanIDo")}
			/>
			<div className={styles.sections}>
				<LanguageSettings />
				<ul className={styles.linkList}>
					{SETTINGS_LINKS.map((link) => (
						<li key={link.to}>
							<Link to={link.to} className={styles.linkCard}>
								<span className={styles.linkTitle}>
									{t(`settings.${link.key}.title`)}
								</span>
								<span className={styles.linkDescription}>
									{t(`settings.${link.key}.description`)}
								</span>
							</Link>
						</li>
					))}
				</ul>
			</div>
		</section>
	);
}
