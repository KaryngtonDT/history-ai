import { useCallback, useEffect, useState } from "react";
import { historyService } from "@/services/history/HistoryService";
import type {
	ComparisonResult,
	ExecutionHistory,
} from "@/services/history/types";
import { ExecutionComparison } from "../ExecutionComparison";
import { VersionTimeline } from "../VersionTimeline";
import styles from "./ExecutionHistoryPanel.module.css";

interface ExecutionHistoryPanelProps {
	videoId: string | null;
}

export function ExecutionHistoryPanel({ videoId }: ExecutionHistoryPanelProps) {
	const [history, setHistory] = useState<ExecutionHistory | null>(null);
	const [selectedVersion, setSelectedVersion] = useState<number | null>(null);
	const [compareVersion, setCompareVersion] = useState<number | null>(null);
	const [comparison, setComparison] = useState<ComparisonResult | null>(null);
	const [loading, setLoading] = useState(false);
	const [comparing, setComparing] = useState(false);
	const [reprocessing, setReprocessing] = useState(false);

	const loadHistory = useCallback(() => {
		if (!videoId) {
			setHistory(null);
			return;
		}

		setLoading(true);

		void historyService
			.loadHistory(videoId)
			.then((loadedHistory) => {
				setHistory(loadedHistory);
				setSelectedVersion(loadedHistory.versions[0]?.versionNumber ?? null);
				setCompareVersion(loadedHistory.versions[1]?.versionNumber ?? null);
			})
			.catch(() => {
				setHistory(null);
			})
			.finally(() => {
				setLoading(false);
			});
	}, [videoId]);

	useEffect(() => {
		loadHistory();
	}, [loadHistory]);

	const handleCompare = (): void => {
		if (
			!videoId ||
			!historyService.canCompare(selectedVersion, compareVersion)
		) {
			return;
		}

		setComparing(true);

		void historyService
			.compareVersions(
				videoId,
				selectedVersion as number,
				compareVersion as number,
			)
			.then(setComparison)
			.finally(() => {
				setComparing(false);
			});
	};

	const handleReprocess = (): void => {
		if (!videoId || selectedVersion === null) {
			return;
		}

		setReprocessing(true);

		void historyService
			.reprocessVersion(videoId, selectedVersion)
			.finally(() => {
				setReprocessing(false);
			});
	};

	if (!videoId) {
		return null;
	}

	return (
		<section className={styles.panel}>
			<div className={styles.header}>
				<h2 className={styles.title}>Version History</h2>
			</div>

			{loading ? (
				<p className={styles.loading}>Loading execution history...</p>
			) : (
				<>
					<VersionTimeline
						versions={history?.versions ?? []}
						selectedVersion={selectedVersion}
						onSelect={setSelectedVersion}
					/>

					<div className={styles.actions}>
						<button
							type="button"
							className={styles.secondaryButton}
							onClick={handleCompare}
							disabled={
								comparing ||
								!historyService.canCompare(selectedVersion, compareVersion)
							}
						>
							Compare
						</button>
						<button
							type="button"
							className={styles.primaryButton}
							onClick={handleReprocess}
							disabled={reprocessing || selectedVersion === null}
						>
							Reprocess
						</button>
					</div>

					<ExecutionComparison comparison={comparison} loading={comparing} />
				</>
			)}
		</section>
	);
}
