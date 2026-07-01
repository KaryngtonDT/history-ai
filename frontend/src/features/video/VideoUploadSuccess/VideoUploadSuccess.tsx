import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { ArtifactJourney } from "@/features/artifacts";
import { VIDEO_PIPELINE_STEPS } from "@/features/product/videoRoutes";
import styles from "./VideoUploadSuccess.module.css";

interface VideoUploadSuccessProps {
	fileName: string;
	videoId: string;
	status: string;
	onUploadAnother: () => void;
}

export function VideoUploadSuccess({
	fileName,
	videoId,
	status,
	onUploadAnother,
}: VideoUploadSuccessProps) {
	return (
		<div className={styles.wrapper}>
			<Card className={styles.card}>
				<div className={styles.header}>
					<p className={styles.title}>Upload complete</p>
					<Badge variant="success">Queued</Badge>
				</div>
				<p className={styles.fileName}>{fileName}</p>
				<p className={styles.message}>
					Video job created. ID:{" "}
					<span className={styles.videoId}>{videoId}</span>
				</p>
				<p className={styles.hint}>
					Status: {status}. Processing will begin soon.
				</p>
				<nav className={styles.pipelineLinks} aria-label="Pipeline steps">
					{VIDEO_PIPELINE_STEPS.map((step) => (
						<Link
							key={step.id}
							to={step.path(videoId)}
							className={styles.pipelineLink}
						>
							{step.label} →
						</Link>
					))}
				</nav>
				<div className={styles.action}>
					<Button variant="secondary" onClick={onUploadAnother}>
						Upload another video
					</Button>
				</div>
			</Card>
			<ArtifactJourney videoId={videoId} />
		</div>
	);
}
