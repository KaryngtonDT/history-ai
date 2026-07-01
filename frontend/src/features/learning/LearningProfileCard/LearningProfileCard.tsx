import { useTranslation } from "@/i18n";
import type {
	LearningAdaptiveHints,
	LearningProfile,
} from "@/services/learning/types";
import styles from "../learning.module.css";

interface LearningProfileCardProps {
	profile: LearningProfile;
	hints: LearningAdaptiveHints;
}

export function LearningProfileCard({
	profile,
	hints,
}: LearningProfileCardProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.card} aria-labelledby="learning-profile-heading">
			<h2 id="learning-profile-heading" className={styles.title}>
				{t("learning.profile.title")}
			</h2>
			<p className={styles.description}>{t("learning.profile.description")}</p>
			<dl className={styles.stats}>
				<div>
					<dt>{t("learning.profile.signals")}</dt>
					<dd>{profile.signals.length}</dd>
				</div>
				<div>
					<dt>{t("learning.profile.insights")}</dt>
					<dd>{profile.insights.length}</dd>
				</div>
				<div>
					<dt>{t("learning.profile.recommendations")}</dt>
					<dd>{profile.recommendations.length}</dd>
				</div>
				<div>
					<dt>{t("learning.profile.adaptiveStatus")}</dt>
					<dd
						className={
							profile.adaptiveRecommendationsEnabled
								? styles.statusOn
								: styles.statusOff
						}
					>
						{profile.adaptiveRecommendationsEnabled
							? t("learning.adaptive.enabled")
							: t("learning.adaptive.disabled")}
					</dd>
				</div>
			</dl>
			<p className={styles.note}>
				{hints.active
					? t("learning.profile.activeNote")
					: t("learning.profile.inactiveNote")}
			</p>
		</section>
	);
}
