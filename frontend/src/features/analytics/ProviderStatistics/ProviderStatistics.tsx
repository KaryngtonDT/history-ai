import { Link } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import type { ProviderStatistics as ProviderStatisticsModel } from "@/services/telemetry/types";
import styles from "./ProviderStatistics.module.css";

interface ProviderStatisticsProps {
	statistics: ProviderStatisticsModel | null;
}

export function ProviderStatistics({ statistics }: ProviderStatisticsProps) {
	const { t } = useTranslation();

	if (!statistics || statistics.providers.length === 0) {
		return (
			<EmptyState
				title={t("workspace.analytics.noProviderStatsTitle")}
				description={t("workspace.analytics.noProviderStatsDescription")}
				action={
					<Link to="/workspace" className={styles.emptyAction}>
						{t("workspace.analytics.noProviderStatsAction")} →
					</Link>
				}
			/>
		);
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
						{t("workspace.analytics.providerRuns", {
							count: provider.invocationCount,
						})}{" "}
						·{" "}
						{t("workspace.analytics.averageDuration", {
							seconds: provider.averageDurationSeconds.toFixed(1),
						})}
					</span>
				</li>
			))}
		</ul>
	);
}
