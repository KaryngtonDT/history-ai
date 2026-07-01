import { useState } from "react";
import { Link, useNavigate } from "react-router";
import { ProcessingModeSelector } from "@/features/orchestrator";
import { PageIntroduction } from "@/features/product";
import { audioSourceService } from "@/services/audioSource/AudioSourceService";
import type { ProcessingMode } from "@/services/orchestrator/types";
import { ValidationError } from "@/shared/errors";
import { AudioDropzone } from "../AudioDropzone";
import styles from "./AudioUploadPanel.module.css";

const UPLOAD_FLOW_ERROR =
	"Could not upload the audio file. Check that the backend is running and try again.";

type UploadPhase = "idle" | "uploading" | "success" | "error";

export function AudioUploadPanel() {
	const navigate = useNavigate();
	const [phase, setPhase] = useState<UploadPhase>("idle");
	const [fileName, setFileName] = useState("");
	const [progress, setProgress] = useState(0);
	const [errorMessage, setErrorMessage] = useState("");
	const [audioId, setAudioId] = useState("");
	const [processingMode, setProcessingMode] =
		useState<ProcessingMode>("automatic");

	const reset = () => {
		setPhase("idle");
		setFileName("");
		setProgress(0);
		setErrorMessage("");
		setAudioId("");
	};

	const handleFileSelected = async (file: File) => {
		setFileName(file.name);
		setProgress(0);
		setPhase("uploading");

		try {
			const result = await audioSourceService.uploadAudio(file, {
				processingMode,
				onProgress: setProgress,
			});
			setAudioId(result.audioId);
			setPhase("success");
		} catch (error) {
			setErrorMessage(
				error instanceof ValidationError ? error.message : UPLOAD_FLOW_ERROR,
			);
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Create"
				title="Transform audio"
				description="Upload a podcast, lecture, or recording. History AI transcribes, translates, and prepares knowledge artifacts."
				whatCanIDo="Choose your AI mode, upload audio, then open the overview to follow processing."
			/>

			<ProcessingModeSelector
				mode={processingMode}
				onChange={setProcessingMode}
			/>

			{phase === "idle" ? (
				<AudioDropzone onFileSelected={handleFileSelected} />
			) : null}

			{phase === "uploading" ? (
				<div className={styles.progress} role="status">
					<p>Uploading {fileName}…</p>
					<progress max={100} value={progress} />
				</div>
			) : null}

			{phase === "success" ? (
				<div className={styles.success}>
					<p>Audio queued for processing.</p>
					<div className={styles.actions}>
						<Link to={`/audio/${audioId}`} className={styles.primaryLink}>
							Open overview
						</Link>
						<button type="button" onClick={() => navigate("/")}>
							Back to Home
						</button>
					</div>
				</div>
			) : null}

			{phase === "error" ? (
				<div className={styles.error}>
					<p>{errorMessage}</p>
					<button type="button" onClick={reset}>
						Try again
					</button>
				</div>
			) : null}
		</div>
	);
}
