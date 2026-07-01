import { Link } from "react-router";
import { videoPipelinePath } from "@/features/product/videoRoutes";
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
	if (videos.length === 0) {
		return (
			<p className={styles.empty}>
				Add videos to this project to start batch processing.
			</p>
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
					<Link
						to={videoPipelinePath("transcript", video.videoId)}
						className={styles.pipelineLink}
					>
						Open pipeline →
					</Link>
					{onRemoveVideo ? (
						<button
							type="button"
							className={styles.removeButton}
							onClick={() => onRemoveVideo(video.videoId)}
							disabled={removingVideoId === video.videoId}
							aria-label={`Remove ${video.filename}`}
						>
							Remove
						</button>
					) : null}
				</li>
			))}
		</ul>
	);
}
