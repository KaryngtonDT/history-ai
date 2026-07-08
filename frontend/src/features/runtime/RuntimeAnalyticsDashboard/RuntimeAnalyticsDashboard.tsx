import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n/useTranslation";
import type { RuntimeEngineAnalytics } from "@/services/runtime/analyticsTypes";
import { runtimeService } from "@/services/runtime/RuntimeService";
import styles from "./RuntimeAnalyticsDashboard.module.css";

function formatMinutes(seconds: number | null): string {
	if (seconds == null || seconds <= 0) {
		return "—";
	}

	return `${Math.ceil(seconds / 60)} min`;
}

export function RuntimeAnalyticsDashboard() {
	const { t } = useTranslation();
	const [engines, setEngines] = useState<RuntimeEngineAnalytics[]>([]);
	const [error, setError] = useState<string | null>(null);

	useEffect(() => {
		void runtimeService
			.listEngineAnalytics()
			.then(setEngines)
			.catch(() => setError(t("settings.runtime.analytics.loadFailed")));
	}, [t]);

	if (error) {
		return <p className={styles.error}>{error}</p>;
	}

	if (engines.length === 0) {
		return (
			<p className={styles.empty}>{t("settings.runtime.analytics.empty")}</p>
		);
	}

	return (
		<div className={styles.root} data-testid="runtime-analytics-dashboard">
			{engines.map((engine) => (
				<article key={engine.engineId} className={styles.card}>
					<h3>{engine.engineId}</h3>
					<dl className={styles.metrics}>
						<div>
							<dt>{t("settings.runtime.analytics.executions")}</dt>
							<dd>{engine.executionCount}</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.average")}</dt>
							<dd>{formatMinutes(engine.averageDurationSeconds)}</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.fastest")}</dt>
							<dd>{formatMinutes(engine.fastestDurationSeconds)}</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.slowest")}</dt>
							<dd>{formatMinutes(engine.slowestDurationSeconds)}</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.averageError")}</dt>
							<dd>
								{engine.averageEstimationErrorSeconds != null
									? `${engine.averageEstimationErrorSeconds}s`
									: "—"}
							</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.successRate")}</dt>
							<dd>
								{engine.successRate != null ? `${engine.successRate}%` : "—"}
							</dd>
						</div>
						<div>
							<dt>{t("settings.runtime.analytics.relativeSpeed")}</dt>
							<dd>{engine.relativeSpeedLabel ?? "—"}</dd>
						</div>
					</dl>
				</article>
			))}
		</div>
	);
}
