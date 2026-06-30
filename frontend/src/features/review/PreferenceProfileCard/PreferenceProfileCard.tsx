import type { PreferenceProfile } from "@/services/review/types";
import styles from "./PreferenceProfileCard.module.css";

interface PreferenceProfileCardProps {
	profile: PreferenceProfile | null;
}

export function PreferenceProfileCard({ profile }: PreferenceProfileCardProps) {
	if (!profile) {
		return (
			<section className={styles.card}>
				<h3 className={styles.title}>Preference Profile</h3>
				<p className={styles.empty}>
					Submit reviews to build your adaptive preference profile.
				</p>
			</section>
		);
	}

	return (
		<section className={styles.card}>
			<h3 className={styles.title}>Preference Profile</h3>
			<div className={styles.grid}>
				<div className={styles.row}>
					<span className={styles.label}>Translation style</span>
					<span>{profile.translationStyle}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>Voice stability</span>
					<span>{profile.voiceStability}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>Rendering preset</span>
					<span>{profile.renderingPreset}</span>
				</div>
				<div className={styles.row}>
					<span className={styles.label}>Lip sync strength</span>
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
