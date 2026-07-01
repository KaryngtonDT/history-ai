import { useTranslation } from "@/i18n";
import type { PreferenceProfile } from "@/services/review/types";
import styles from "./PreferenceProfileCard.module.css";

interface PreferenceProfileCardProps {
	profile: PreferenceProfile | null;
}

export function PreferenceProfileCard({ profile }: PreferenceProfileCardProps) {
	const { t } = useTranslation();

	if (!profile) {
		return (
			<section className={styles.card}>
				<h3 className={styles.title}>
					{t("workspace.review.preferenceProfile.title")}
				</h3>
				<p className={styles.empty}>
					{t("workspace.review.preferenceProfile.empty")}
				</p>
			</section>
		);
	}

	return (
		<section className={styles.card}>
			<h3 className={styles.title}>
				{t("workspace.review.preferenceProfile.title")}
			</h3>
			<div className={styles.grid}>
				<div className={styles.row}>
					<span className={styles.label}>
						{t("workspace.review.preferenceProfile.translationStyle")}
					</span>
					<span>{profile.translationStyle}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>
						{t("workspace.review.preferenceProfile.voiceStability")}
					</span>
					<span>{profile.voiceStability}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>
						{t("workspace.review.preferenceProfile.renderingPreset")}
					</span>
					<span>{profile.renderingPreset}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>
						{t("workspace.review.preferenceProfile.lipSyncStrength")}
					</span>
					<span>{profile.lipSyncStrength}</span>
				</div>
			</div>
			<ul className={styles.explanationList}>
				{profile.explanationLines.map((line) => (
					<li key={line}>{line}</li>
				))}
			</ul>
		</section>
	);
}
