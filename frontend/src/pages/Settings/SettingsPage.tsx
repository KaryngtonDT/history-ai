import { Link } from "react-router";
import { PageIntroduction } from "@/features/product";
import styles from "./SettingsPage.module.css";

const SETTINGS_LINKS = [
	{
		to: "/settings/ai",
		title: "AI Engines",
		description: "View registered providers and capabilities (Sprint 34).",
	},
	{
		to: "/settings/pipeline",
		title: "Pipeline Configuration",
		description: "Assign engines to each processing stage (Sprint 39).",
	},
] as const;

export function SettingsPage() {
	return (
		<section>
			<PageIntroduction
				eyebrow="Settings"
				title="Settings"
				description="Configure how History AI processes your videos."
				whatCanIDo="Choose AI engines and pipeline stages. Changes apply to the next processing run."
			/>
			<ul className={styles.linkList}>
				{SETTINGS_LINKS.map((link) => (
					<li key={link.to}>
						<Link to={link.to} className={styles.linkCard}>
							<span className={styles.linkTitle}>{link.title}</span>
							<span className={styles.linkDescription}>{link.description}</span>
						</Link>
					</li>
				))}
			</ul>
		</section>
	);
}
