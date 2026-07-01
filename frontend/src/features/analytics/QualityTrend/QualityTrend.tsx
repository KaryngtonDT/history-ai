import { useTranslation } from "@/i18n";
import type {
	PipelineTelemetry,
	RecentTelemetryError,
} from "@/services/telemetry/types";
import styles from "./QualityTrend.module.css";

interface QualityTrendProps {
	records: PipelineTelemetry[];
	recentErrors: RecentTelemetryError[];
}

export function QualityTrend({ records, recentErrors }: QualityTrendProps) {
	const { t } = useTranslation();
	const qualityRecords = records.filter(
		(record) => record.qualityScore !== null,
	);

	return (
		<div>
			{qualityRecords.length === 0 ? (
				<p className={styles.empty}>
					{t("workspace.analytics.noQualityTrendData")}
				</p>
			) : (
				<ul className={styles.list}>
					{qualityRecords.slice(0, 5).map((record) => (
						<li key={record.id} className={styles.item}>
							<span>{record.videoId.slice(0, 8)}</span>
							<span className={styles.score}>{record.qualityScore}</span>
							<span className={styles.date}>
								{new Date(record.recordedAt).toLocaleDateString()}
							</span>
						</li>
					))}
				</ul>
			)}

			{recentErrors.length > 0 ? (
				<div className={styles.errors}>
					<h3 className={styles.errorsTitle}>
						{t("workspace.analytics.lastErrors")}
					</h3>
					{recentErrors.map((error) => (
						<div
							key={`${error.message}-${error.recordedAt}`}
							className={styles.errorItem}
						>
							<span>{error.message}</span>
							<span className={styles.errorStatus}>{error.status}</span>
						</div>
					))}
				</div>
			) : null}
		</div>
	);
}
