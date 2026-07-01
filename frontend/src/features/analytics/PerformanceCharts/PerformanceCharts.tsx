import { Link } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import type {
	ExecutionMetric,
	PipelineTelemetry,
} from "@/services/telemetry/types";
import styles from "./PerformanceCharts.module.css";

interface PerformanceChartsProps {
	records: PipelineTelemetry[];
}

interface PerformanceSample {
	id: string;
	metric: ExecutionMetric;
}

export function PerformanceCharts({ records }: PerformanceChartsProps) {
	const { t } = useTranslation();
	const samples: PerformanceSample[] = records
		.map((record) => ({
			id: record.id,
			metric: record.metrics.find((entry) => entry.type === "processing_time"),
		}))
		.filter(
			(sample): sample is PerformanceSample => sample.metric !== undefined,
		)
		.slice(0, 5);

	if (samples.length === 0) {
		return (
			<EmptyState
				title={t("workspace.analytics.noPerformanceTitle")}
				description={t("workspace.analytics.noPerformanceDescription")}
				action={
					<Link to="/workspace" className={styles.emptyAction}>
						{t("workspace.analytics.noPerformanceAction")} →
					</Link>
				}
			/>
		);
	}

	const maxValue = Math.max(...samples.map((sample) => sample.metric.value), 1);

	return (
		<div className={styles.chart}>
			{samples.map((sample, index) => (
				<div key={sample.id} className={styles.row}>
					<span className={styles.label}>
						{t("workspace.analytics.runLabel", { index: index + 1 })}
					</span>
					<div className={styles.barTrack}>
						<div
							className={styles.barFill}
							style={{
								width: `${(sample.metric.value / maxValue) * 100}%`,
							}}
						/>
					</div>
					<span className={styles.value}>
						{Math.round(sample.metric.value)}s
					</span>
				</div>
			))}
		</div>
	);
}
