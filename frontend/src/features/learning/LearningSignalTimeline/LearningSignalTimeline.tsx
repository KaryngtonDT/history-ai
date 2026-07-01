import { useTranslation } from "@/i18n";
import type { LearningSignal } from "@/services/learning/types";
import styles from "../learning.module.css";

interface LearningSignalTimelineProps {
	signals: LearningSignal[];
}

export function LearningSignalTimeline({
	signals,
}: LearningSignalTimelineProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.card} aria-labelledby="learning-signals-heading">
			<h2 id="learning-signals-heading" className={styles.title}>
				{t("learning.signals.title")}
			</h2>
			<p className={styles.description}>{t("learning.signals.description")}</p>
			{signals.length === 0 ? (
				<p className={styles.empty}>{t("learning.signals.empty")}</p>
			) : (
				<ul className={styles.list}>
					{signals.map((signal) => (
						<li key={signal.id} className={styles.listItem}>
							<strong>{signal.type}</strong>
							<span>{String(signal.context.summary ?? "")}</span>
							<span className={styles.meta}>{signal.recordedAt}</span>
						</li>
					))}
				</ul>
			)}
		</section>
	);
}
