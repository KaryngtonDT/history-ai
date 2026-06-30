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
	if (versions.length === 0) {
		return <p className={styles.empty}>No execution history recorded yet.</p>;
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
							<span className={styles.version}>V{version.versionNumber}</span>
							<span className={styles.profile}>
								{historyService.formatProfile(version.optimizationProfile)}
							</span>
							<span className={styles.score}>Score {version.qualityScore}</span>
						</button>
					</li>
				);
			})}
		</ul>
	);
}
