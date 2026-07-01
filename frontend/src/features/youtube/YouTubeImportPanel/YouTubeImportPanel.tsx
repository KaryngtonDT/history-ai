import { useState } from "react";
import { Link } from "react-router";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { ProcessingModeSelector } from "@/features/orchestrator";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n/useTranslation";
import type { ProcessingMode } from "@/services/orchestrator/types";
import type { YouTubeMetadata } from "@/services/youtubeSource/types";
import { formatDuration } from "@/services/youtubeSource/types";
import { youtubeSourceService } from "@/services/youtubeSource/YouTubeSourceService";
import { ValidationError } from "@/shared/errors";
import styles from "./YouTubeImportPanel.module.css";

type Phase = "idle" | "previewing" | "importing" | "success" | "error";

export function YouTubeImportPanel() {
	const { t } = useTranslation();
	const [url, setUrl] = useState("");
	const [phase, setPhase] = useState<Phase>("idle");
	const [metadata, setMetadata] = useState<YouTubeMetadata | null>(null);
	const [videoId, setVideoId] = useState("");
	const [errorMessage, setErrorMessage] = useState("");
	const [processingMode, setProcessingMode] =
		useState<ProcessingMode>("automatic");

	const handlePreview = async () => {
		setPhase("previewing");
		setErrorMessage("");

		try {
			const preview = await youtubeSourceService.previewYouTube(url);
			setMetadata(preview);
			setPhase("idle");
		} catch (error) {
			setMetadata(null);
			setErrorMessage(
				error instanceof ValidationError
					? error.message
					: t("pipeline.youtube.previewFailed"),
			);
			setPhase("error");
		}
	};

	const handleImport = async () => {
		setPhase("importing");
		setErrorMessage("");

		try {
			const result = await youtubeSourceService.importYouTube(url, {
				processingMode,
			});
			setVideoId(result.videoId);
			setMetadata(result.metadata);
			setPhase("success");
		} catch (error) {
			setErrorMessage(
				error instanceof ValidationError
					? error.message
					: t("pipeline.youtube.importFailed"),
			);
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("pipeline.youtube.eyebrow")}
				title={t("pipeline.youtube.title")}
				description={t("pipeline.youtube.description")}
				whatCanIDo={t("pipeline.youtube.whatCanIDo")}
			/>

			<Card className={styles.formCard}>
				<label className={styles.label} htmlFor="youtube-url">
					{t("pipeline.youtube.urlLabel")}
				</label>
				<input
					id="youtube-url"
					className={styles.input}
					type="url"
					placeholder={t("pipeline.youtube.urlPlaceholder")}
					value={url}
					onChange={(event) => setUrl(event.target.value)}
				/>
				<div className={styles.actions}>
					<Button
						variant="secondary"
						disabled={!url || phase === "previewing" || phase === "importing"}
						onClick={() => void handlePreview()}
					>
						{t("pipeline.youtube.previewCta")}
					</Button>
					<Button
						variant="primary"
						disabled={!url || phase === "importing"}
						onClick={() => void handleImport()}
					>
						{t("pipeline.youtube.importCta")}
					</Button>
				</div>
			</Card>

			<ProcessingModeSelector
				mode={processingMode}
				onChange={setProcessingMode}
			/>

			{metadata ? (
				<Card className={styles.previewCard}>
					{metadata.thumbnailUrl ? (
						<img
							className={styles.thumbnail}
							src={metadata.thumbnailUrl}
							alt=""
						/>
					) : null}
					<div>
						<h2 className={styles.previewTitle}>{metadata.title}</h2>
						<p className={styles.previewMeta}>
							{formatDuration(metadata.durationSeconds)}
							{metadata.channelName ? ` · ${metadata.channelName}` : ""}
						</p>
					</div>
				</Card>
			) : null}

			{phase === "importing" ? (
				<p role="status">{t("pipeline.youtube.importingStatus")}</p>
			) : null}

			{phase === "success" ? (
				<Card className={styles.successCard}>
					<p>{t("pipeline.youtube.importSuccess")}</p>
					<Link
						to={`/video/${videoId}`}
						className={styles.primaryLink}
						aria-label={t("pipeline.youtube.openVideoOverviewAria")}
					>
						{t("pipeline.youtube.openVideoOverview")}
					</Link>
				</Card>
			) : null}

			{phase === "error" ? (
				<p className={styles.error}>{errorMessage}</p>
			) : null}
		</div>
	);
}
