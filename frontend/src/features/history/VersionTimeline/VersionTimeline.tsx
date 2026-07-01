import { Link } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import { historyService } from "@/services/history/HistoryService";
import type { ExecutionVersion } from "@/services/history/types";
import styles from "./VersionTimeline.module.css";

interface VersionTimelineProps {
	versions: ExecutionVersion[];
	selectedVersion: number | null;
	onSelect: (versionNumber: number) => void;
}

export function VersionTimeline({
	versions,
	selectedVersion,
	onSelect,
}: VersionTimelineProps) {
	const { t } = useTranslation();

	if (versions.length === 0) {
		return (
			<EmptyState
				title={t("workspace.history.emptyTitle")}
				description={t("workspace.history.emptyDescription")}
				action={
					<Link to="/video/upload" className={styles.emptyAction}>
						{t("workspace.history.emptyAction")} →
					</Link>
				}
			/>
		);
	}

	return (
		<ul className={styles.list}>
			{historyService.sortedVersions(versions).map((version) => {
				const selected = selectedVersion === version.versionNumber;

				return (
					<li key={version.versionNumber}>
						<button
							type="button"
							className={
								selected ? `${styles.item} ${styles.selected}` : styles.item
							}
							onClick={() => onSelect(version.versionNumber)}
						>
							<span className={styles.version}>
								{t("workspace.history.versionLabel", {
									version: version.versionNumber,
								})}
							</span>
							<span className={styles.profile}>
								{historyService.formatProfile(version.optimizationProfile)}
							</span>
							<span className={styles.score}>
								{t("workspace.history.scoreLabel", {
									score: version.qualityScore,
								})}
							</span>
						</button>
					</li>
				);
			})}
		</ul>
	);
}
