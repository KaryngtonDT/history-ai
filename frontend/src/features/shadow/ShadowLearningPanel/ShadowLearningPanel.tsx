import { useTranslation } from "@/i18n/useTranslation";
import type {
	SessionLearningState,
	SessionTeachingStrategy,
} from "@/services/shadow/types";
import styles from "./ShadowLearningPanel.module.css";

interface ShadowLearningPanelProps {
	learning: SessionLearningState | null;
	strategy: SessionTeachingStrategy | null;
	disabled?: boolean;
	onAdaptiveToggle: (enabled: boolean) => void;
}

function meterLevel(value: string): number {
	switch (value) {
		case "high":
		case "growing":
		case "fast":
		case "advanced":
			return 4;
		case "medium":
		case "stable":
		case "normal":
		case "intermediate":
			return 3;
		case "low":
		case "slow":
		case "easy":
			return 2;
		case "struggling":
			return 1;
		default:
			return 2;
	}
}

function Meter({ label, value }: { label: string; value: string }) {
	const level = meterLevel(value);

	return (
		<div className={styles.meterRow}>
			<div className={styles.meterHeader}>
				<span>{label}</span>
				<span className={styles.meterValue}>{value}</span>
			</div>
			<div className={styles.meterTrack} aria-hidden="true">
				{Array.from({ length: 4 }, (_, index) => (
					<span
						key={label + String(index)}
						className={index < level ? styles.meterFill : styles.meterEmpty}
					/>
				))}
			</div>
		</div>
	);
}

export function ShadowLearningPanel({
	learning,
	strategy,
	disabled = false,
	onAdaptiveToggle,
}: ShadowLearningPanelProps) {
	const { t } = useTranslation();

	if (!learning) {
		return null;
	}

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("pipeline.shadow.sessionLearning.title")}</h3>
				<label className={styles.toggle}>
					<input
						type="checkbox"
						checked={learning.adaptiveEnabled}
						disabled={disabled}
						onChange={(event) => onAdaptiveToggle(event.target.checked)}
					/>
					<span>{t("pipeline.shadow.sessionLearning.adaptive")}</span>
				</label>
			</header>

			<Meter
				label={t("pipeline.shadow.sessionLearning.attention")}
				value={learning.attention}
			/>
			<Meter
				label={t("pipeline.shadow.sessionLearning.confidence")}
				value={learning.confidence}
			/>

			<dl className={styles.meta}>
				<div>
					<dt>{t("pipeline.shadow.sessionLearning.currentStrategy")}</dt>
					<dd>{strategy?.kind ?? learning.strategyKind}</dd>
				</div>
				<div>
					<dt>{t("pipeline.shadow.sessionLearning.difficulty")}</dt>
					<dd>{learning.difficulty}</dd>
				</div>
				<div>
					<dt>{t("pipeline.shadow.sessionLearning.speakingPace")}</dt>
					<dd>{learning.speakingPace}</dd>
				</div>
			</dl>

			{learning.adjustments.length > 0 ? (
				<div className={styles.history}>
					<h4>{t("pipeline.shadow.sessionLearning.historyTitle")}</h4>
					<ul>
						{learning.adjustments
							.slice()
							.reverse()
							.map((item) => (
								<li key={`${item.timeSeconds}-${item.label}`}>
									<strong>
										{formatTime(item.timeSeconds)} — {item.label}
									</strong>
									<p>{item.reason}</p>
								</li>
							))}
					</ul>
				</div>
			) : null}
		</section>
	);
}

function formatTime(seconds: number): string {
	const minutes = Math.floor(seconds / 60);
	const remainder = Math.floor(seconds % 60);

	return `${String(minutes).padStart(2, "0")}:${String(remainder).padStart(2, "0")}`;
}
