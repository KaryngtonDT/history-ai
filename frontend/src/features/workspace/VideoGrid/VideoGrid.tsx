import { Link } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import type { ProjectVideo } from "@/services/workspace/types";
import styles from "./VideoGrid.module.css";

interface VideoGridProps {
	videos: ProjectVideo[];
	onRemoveVideo?: (videoId: string) => void;
	removingVideoId?: string | null;
}

export function VideoGrid({
	videos,
	onRemoveVideo,
	removingVideoId = null,
}: VideoGridProps) {
	const { t } = useTranslation();

	if (videos.length === 0) {
		return (
			<EmptyState
				title={t("workspace.videoGrid.emptyTitle")}
				description={t("workspace.videoGrid.emptyDescription")}
				action={
					<Link to="/video/upload" className={styles.emptyAction}>
						{t("workspace.videoGrid.emptyAction")} →
					</Link>
				}
			/>
		);
	}

	return (
		<ul className={styles.grid}>
			{videos.map((video) => (
				<li key={video.videoId} className={styles.item}>
					<span className={styles.checkmark} aria-hidden="true">
						✓
					</span>
					<span className={styles.filename}>{video.filename}</span>
					<Link to={`/video/${video.videoId}`} className={styles.pipelineLink}>
						{t("workspace.videoGrid.openPipeline")}
					</Link>
					{onRemoveVideo ? (
						<button
							type="button"
							className={styles.removeButton}
							onClick={() => onRemoveVideo(video.videoId)}
							disabled={removingVideoId === video.videoId}
							aria-label={t("workspace.videoGrid.removeAria", {
								filename: video.filename,
							})}
						>
							{t("workspace.videoGrid.remove")}
						</button>
					) : null}
				</li>
			))}
		</ul>
	);
}
