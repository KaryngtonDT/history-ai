import { useTranslation } from "@/i18n";
import type { LearningAdaptiveHints } from "@/services/learning/types";
import styles from "../learning.module.css";

interface AdaptiveModeToggleProps {
	active: boolean;
	hints: LearningAdaptiveHints;
}

export function AdaptiveModeToggle({ active, hints }: AdaptiveModeToggleProps) {
	const { t } = useTranslation();

	return (
		<section
			className={styles.card}
			aria-labelledby="learning-adaptive-heading"
		>
			<h2 id="learning-adaptive-heading" className={styles.title}>
				{t("learning.adaptive.statusTitle")}
			</h2>
			<p className={styles.description}>
				{active
					? t("learning.adaptive.statusEnabled")
					: t("learning.adaptive.statusDisabled")}
			</p>
			{active && hints.appliedRecommendations.length > 0 ? (
				<ul className={styles.list}>
					{hints.appliedRecommendations.map((item) => (
						<li key={item}>{item}</li>
					))}
				</ul>
			) : (
				<p className={styles.empty}>{t("learning.adaptive.noAppliedHints")}</p>
			)}
		</section>
	);
}
