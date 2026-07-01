import { AdaptiveModeToggle } from "@/features/learning/AdaptiveModeToggle";
import { LearningInsightList } from "@/features/learning/LearningInsightList";
import { LearningPreferenceControls } from "@/features/learning/LearningPreferenceControls";
import { LearningProfileCard } from "@/features/learning/LearningProfileCard";
import { LearningRecommendationList } from "@/features/learning/LearningRecommendationList";
import { LearningResetPanel } from "@/features/learning/LearningResetPanel";
import { LearningSignalTimeline } from "@/features/learning/LearningSignalTimeline";
import { useTranslation } from "@/i18n";
import type { LearningRecommendationsResponse } from "@/services/learning/types";
import styles from "../learning.module.css";

interface LearningCenterProps {
	data: LearningRecommendationsResponse;
	onToggleAdaptive: (enabled: boolean) => Promise<void>;
	onReset: () => Promise<void>;
	isUpdating: boolean;
}

export function LearningCenter({
	data,
	onToggleAdaptive,
	onReset,
	isUpdating,
}: LearningCenterProps) {
	const { t } = useTranslation();
	const { profile, adaptiveHints } = data;

	return (
		<div className={styles.learningCenter}>
			<LearningProfileCard profile={profile} hints={adaptiveHints} />

			<LearningPreferenceControls
				enabled={profile.adaptiveRecommendationsEnabled}
				onToggle={onToggleAdaptive}
				disabled={isUpdating}
			/>

			<AdaptiveModeToggle
				active={profile.adaptiveRecommendationsEnabled}
				hints={adaptiveHints}
			/>

			<div className={styles.grid}>
				<LearningSignalTimeline signals={profile.signals} />
				<LearningInsightList insights={profile.insights} />
			</div>

			<LearningRecommendationList recommendations={profile.recommendations} />

			<section className={styles.card}>
				<h2 className={styles.cardTitle}>
					{t("learning.sections.shadow.title")}
				</h2>
				<p className={styles.cardDescription}>
					{t("learning.sections.shadow.description")}
				</p>
				<div className={styles.hintGrid}>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.shadow.vocabularyGaps")}</span>
						<span>
							{
								profile.insights.filter(
									(item) => item.type === "vocabulary_gap",
								).length
							}
						</span>
					</div>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.shadow.challengeLevel")}</span>
						<span>
							{adaptiveHints.challengeLevel ?? t("learning.notAvailable")}
						</span>
					</div>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.shadow.voiceLanguage")}</span>
						<span>
							{adaptiveHints.voiceLanguage ?? t("learning.notAvailable")}
						</span>
					</div>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.shadow.explanationStyle")}</span>
						<span>
							{adaptiveHints.explanationStyle ?? t("learning.notAvailable")}
						</span>
					</div>
				</div>
			</section>

			<section className={styles.card}>
				<h2 className={styles.cardTitle}>
					{t("learning.sections.director.title")}
				</h2>
				<p className={styles.cardDescription}>
					{t("learning.sections.director.description")}
				</p>
				<div className={styles.hintGrid}>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.director.providerPreference")}</span>
						<span>
							{adaptiveHints.preferredProvider ?? t("learning.notAvailable")}
						</span>
					</div>
					<div className={styles.hintRow}>
						<span>{t("learning.sections.director.translationStyle")}</span>
						<span>
							{adaptiveHints.translationStyle ?? t("learning.notAvailable")}
						</span>
					</div>
				</div>
			</section>

			<LearningResetPanel onReset={onReset} disabled={isUpdating} />
		</div>
	);
}
