import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { ArtifactJourney } from "@/features/artifacts";
import {
	getVideoPipelineStepLabel,
	VIDEO_PIPELINE_STEPS,
} from "@/features/product/videoRoutes";
import { useTranslation } from "@/i18n/useTranslation";
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
	const { t } = useTranslation();

	return (
		<div className={styles.wrapper}>
			<Card className={styles.card}>
				<div className={styles.header}>
					<p className={styles.title}>
						{t("pipeline.upload.videoUploadComplete")}
					</p>
					<Badge variant="success">{t("pipeline.upload.videoQueued")}</Badge>
				</div>
				<p className={styles.fileName}>{fileName}</p>
				<p className={styles.message}>
					{t("pipeline.upload.videoJobCreated")}{" "}
					<span className={styles.videoId}>{videoId}</span>
				</p>
				<p className={styles.hint}>
					{t("pipeline.upload.videoStatusHint", { status })}
				</p>
				<nav
					className={styles.pipelineLinks}
					aria-label={t("pipeline.upload.videoPipelineLinksAria")}
				>
					{VIDEO_PIPELINE_STEPS.map((step) => (
						<Link
							key={step.id}
							to={step.path(videoId)}
							className={styles.pipelineLink}
						>
							{getVideoPipelineStepLabel(t, step.id)} →
						</Link>
					))}
				</nav>
				<div className={styles.action}>
					<Button variant="secondary" onClick={onUploadAnother}>
						{t("pipeline.upload.uploadAnotherVideo")}
					</Button>
				</div>
			</Card>
			<ArtifactJourney videoId={videoId} />
		</div>
	);
}
