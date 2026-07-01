import { useState } from "react";
import { Link } from "react-router";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { ProcessingModeSelector } from "@/features/orchestrator";
import { PageIntroduction } from "@/features/product";
import type { ProcessingMode } from "@/services/orchestrator/types";
import type { YouTubeMetadata } from "@/services/youtubeSource/types";
import { formatDuration } from "@/services/youtubeSource/types";
import { youtubeSourceService } from "@/services/youtubeSource/YouTubeSourceService";
import { ValidationError } from "@/shared/errors";
import styles from "./YouTubeImportPanel.module.css";

type Phase = "idle" | "previewing" | "importing" | "success" | "error";

export function YouTubeImportPanel() {
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
					: "Could not preview this YouTube video.",
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
					: "Could not import this YouTube video.",
			);
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Create"
				title="Import from YouTube"
				description="Paste any public YouTube link. History AI downloads the video and runs the same localization pipeline as uploaded files."
				whatCanIDo="Preview metadata, choose AI mode, then import to open the video overview."
			/>

			<Card className={styles.formCard}>
				<label className={styles.label} htmlFor="youtube-url">
					YouTube URL
				</label>
				<input
					id="youtube-url"
					className={styles.input}
					type="url"
					placeholder="https://www.youtube.com/watch?v=..."
					value={url}
					onChange={(event) => setUrl(event.target.value)}
				/>
				<div className={styles.actions}>
					<Button
						variant="secondary"
						disabled={!url || phase === "previewing" || phase === "importing"}
						onClick={() => void handlePreview()}
					>
						Preview
					</Button>
					<Button
						variant="primary"
						disabled={!url || phase === "importing"}
						onClick={() => void handleImport()}
					>
						Import video
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
				<p role="status">Importing and queuing pipeline…</p>
			) : null}

			{phase === "success" ? (
				<Card className={styles.successCard}>
					<p>Video imported. Pipeline queued.</p>
					<Link
						to={`/video/${videoId}`}
						className={styles.primaryLink}
						aria-label="Open video overview"
					>
						Open video overview →
					</Link>
				</Card>
			) : null}

			{phase === "error" ? (
				<p className={styles.error}>{errorMessage}</p>
			) : null}
		</div>
	);
}
