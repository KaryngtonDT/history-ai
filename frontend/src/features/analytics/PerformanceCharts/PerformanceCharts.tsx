import type { PipelineTelemetry } from "@/services/telemetry/types";
import styles from "./PerformanceCharts.module.css";

interface PerformanceChartsProps {
	records: PipelineTelemetry[];
}

export function PerformanceCharts({ records }: PerformanceChartsProps) {
	const samples = records
		.map((record) => ({
			id: record.id,
			metric: record.metrics.find((entry) => entry.type === "processing_time"),
		}))
		.filter(
			(
				sample,
			): sample is {
				id: string;
				metric: NonNullable<(typeof samples)[number]["metric"]>;
			} => sample.metric !== undefined,
		)
		.slice(0, 5);

	if (samples.length === 0) {
		return <p className={styles.empty}>No performance samples yet.</p>;
	}

	const maxValue = Math.max(...samples.map((sample) => sample.metric.value), 1);

	return (
		<div className={styles.chart}>
			{samples.map((sample, index) => (
				<div key={sample.id} className={styles.row}>
					<span className={styles.label}>Run {index + 1}</span>
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
