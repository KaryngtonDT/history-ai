import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { API_BASE_URL } from "@/config/api";
import {
	formatFileSize,
	formatVideoRenderDuration,
	formatVideoRenderProviderLabel,
	resolveVideoRenderStreamUrl,
	type VideoRender,
} from "@/services/render/types";
import { formatTranslationLanguageLabel } from "@/services/translation/types";
import styles from "./FinalVideoPlayer.module.css";

interface FinalVideoPlayerProps {
	entries: VideoRender[];
	activeLanguage: string | null;
	onSelectLanguage: (language: string) => void;
}

export function FinalVideoPlayer({
	entries,
	activeLanguage,
	onSelectLanguage,
}: FinalVideoPlayerProps) {
	const activeEntry =
		entries.find((entry) => entry.targetLanguage === activeLanguage) ?? null;

	if (entries.length === 0) {
		return (
			<EmptyState
				title="No final video yet"
				description="Render a lip-synced video to produce a downloadable MP4."
			/>
		);
	}

	if (!activeEntry) {
		return null;
	}

	const streamUrl = resolveVideoRenderStreamUrl(
		activeEntry.streamUrl,
		API_BASE_URL,
	);
	const downloadUrl = resolveVideoRenderStreamUrl(
		activeEntry.downloadUrl,
		API_BASE_URL,
	);

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
					<p className={styles.sectionLabel}>Final Video</p>
					<Badge variant="neutral">
						{formatVideoRenderProviderLabel(activeEntry.provider)}
					</Badge>
				</div>

				<video
					className={styles.video}
					src={streamUrl}
					controls
					preload="metadata"
				>
					<track kind="captions" />
				</video>

				<div className={styles.meta}>
					<span>
						{activeEntry.format.toUpperCase()} · {activeEntry.quality}
					</span>
					<span>
						Duration {formatVideoRenderDuration(activeEntry.duration)}
					</span>
					<span>{formatFileSize(activeEntry.fileSizeBytes)}</span>
				</div>

				<div className={styles.actions}>
					<a className={styles.downloadLink} href={downloadUrl} download>
						Download MP4
					</a>
				</div>
			</Card>
		</>
	);
}
