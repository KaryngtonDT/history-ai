import type { ProviderStatistics as ProviderStatisticsModel } from "@/services/telemetry/types";
import styles from "./ProviderStatistics.module.css";

interface ProviderStatisticsProps {
	statistics: ProviderStatisticsModel | null;
}

export function ProviderStatistics({ statistics }: ProviderStatisticsProps) {
	if (!statistics || statistics.providers.length === 0) {
		return <p className={styles.empty}>No provider statistics yet.</p>;
	}

	return (
		<ul className={styles.list}>
			{statistics.providers.map((provider) => (
				<li
					key={`${provider.stage}:${provider.providerId}`}
					className={styles.item}
				>
					<span className={styles.label}>{provider.providerId}</span>
					<span className={styles.meta}>
						{provider.invocationCount} runs · avg{" "}
						{provider.averageDurationSeconds.toFixed(1)}s
					</span>
				</li>
			))}
		</ul>
	);
}
