import { useState } from "react";
import { Link, useNavigate } from "react-router";
import { ProcessingModeSelector } from "@/features/orchestrator";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n/useTranslation";
import { audioSourceService } from "@/services/audioSource/AudioSourceService";
import type { ProcessingMode } from "@/services/orchestrator/types";
import { ValidationError } from "@/shared/errors";
import { AudioDropzone } from "../AudioDropzone";
import styles from "./AudioUploadPanel.module.css";

type UploadPhase = "idle" | "uploading" | "success" | "error";

export function AudioUploadPanel() {
	const { t } = useTranslation();
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
				error instanceof ValidationError
					? error.message
					: t("pipeline.upload.audioErrorFallback"),
			);
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("pipeline.create.audioEyebrow")}
				title={t("pipeline.create.audioTitle")}
				description={t("pipeline.create.audioDescription")}
				whatCanIDo={t("pipeline.create.audioWhatCanIDo")}
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
					<p>{t("pipeline.upload.audioUploadingStatus", { fileName })}</p>
					<progress max={100} value={progress} />
				</div>
			) : null}

			{phase === "success" ? (
				<div className={styles.success}>
					<p>{t("pipeline.upload.audioQueued")}</p>
					<div className={styles.actions}>
						<Link to={`/audio/${audioId}`} className={styles.primaryLink}>
							{t("pipeline.upload.audioOpenOverview")}
						</Link>
						<button type="button" onClick={() => navigate("/")}>
							{t("pipeline.upload.backToHome")}
						</button>
					</div>
				</div>
			) : null}

			{phase === "error" ? (
				<div className={styles.error}>
					<p>{errorMessage}</p>
					<button type="button" onClick={reset}>
						{t("pipeline.upload.tryAgain")}
					</button>
				</div>
			) : null}
		</div>
	);
}
