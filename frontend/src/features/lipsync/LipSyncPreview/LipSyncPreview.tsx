import { useRef } from "react";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { API_BASE_URL } from "@/config/api";
import {
	formatLipSyncDuration,
	formatLipSyncProviderLabel,
	resolveLipSyncStreamUrl,
	type VideoLipSync,
} from "@/services/lipsync/types";
import { formatTranslationLanguageLabel } from "@/services/translation/types";
import styles from "./LipSyncPreview.module.css";

interface LipSyncPreviewProps {
	entries: VideoLipSync[];
	activeLanguage: string | null;
	compareMode: boolean;
	onSelectLanguage: (language: string) => void;
	onCompareModeChange: (enabled: boolean) => void;
}

export function LipSyncPreview({
	entries,
	activeLanguage,
	compareMode,
	onSelectLanguage,
	onCompareModeChange,
}: LipSyncPreviewProps) {
	const syncedRef = useRef<HTMLVideoElement | null>(null);

	const activeEntry =
		entries.find((entry) => entry.targetLanguage === activeLanguage) ?? null;

	if (entries.length === 0) {
		return (
			<EmptyState
				title="No lip-synced video yet"
				description="Generate lip sync after voice cloning is complete."
			/>
		);
	}

	if (!activeEntry) {
		return null;
	}

	const originalUrl = resolveLipSyncStreamUrl(
		activeEntry.originalVideoUrl,
		API_BASE_URL,
	);
	const syncedUrl = resolveLipSyncStreamUrl(
		activeEntry.syncedVideoUrl,
		API_BASE_URL,
	);

	const replaySynced = async () => {
		const element = syncedRef.current;

		if (!element) {
			return;
		}

		element.currentTime = 0;
		await element.play();
	};

	return (
		<>
			<div className={styles.languageTabs}>
				{entries.map((entry) => (
					<button
						key={entry.targetLanguage}
						type="button"
						className={
							entry.targetLanguage === activeLanguage
								? styles.activeTab
								: styles.tab
						}
						onClick={() => onSelectLanguage(entry.targetLanguage)}
					>
						{formatTranslationLanguageLabel(entry.targetLanguage)}
					</button>
				))}
			</div>

			<Card className={styles.preview}>
				<div className={styles.previewHeader}>
					<p className={styles.sectionLabel}>Preview</p>
					<Badge variant="neutral">
						{formatLipSyncProviderLabel(activeEntry.provider)}
					</Badge>
				</div>

				<label className={styles.compareToggle}>
					<input
						type="checkbox"
						checked={compareMode}
						onChange={(event) => onCompareModeChange(event.target.checked)}
					/>
					Before / after comparison
				</label>

				<div className={compareMode ? styles.compareGrid : styles.singleColumn}>
					{compareMode ? (
						<div className={styles.playerBlock}>
							<p className={styles.playerLabel}>Original video</p>
							<video
								className={styles.video}
								src={originalUrl}
								controls
								preload="metadata"
							>
								<track kind="captions" />
							</video>
						</div>
					) : null}

					<div className={styles.playerBlock}>
						<p className={styles.playerLabel}>
							{compareMode ? "Lip-synced" : "Synced video"}
						</p>
						<video
							ref={syncedRef}
							className={styles.video}
							src={syncedUrl}
							controls
							preload="metadata"
						>
							<track kind="captions" />
						</video>
						<div className={styles.actions}>
							<Button type="button" onClick={() => void replaySynced()}>
								Replay
							</Button>
						</div>
					</div>
				</div>

				<p className={styles.durationMeta}>
					Duration {formatLipSyncDuration(activeEntry.duration)}
				</p>
			</Card>
		</>
	);
}
