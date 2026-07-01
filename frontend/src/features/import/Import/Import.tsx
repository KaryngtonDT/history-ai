import { useState } from "react";
import { useNavigate } from "react-router";
import { useTranslation } from "@/i18n";
import { contentService } from "@/services/content/ContentService";
import { processingService } from "@/services/processing/ProcessingService";
import { ImportHeader } from "../ImportHeader";
import { PdfDropzone } from "../PdfDropzone";
import type { ImportPhase } from "../types";
import { UploadError } from "../UploadError";
import { UploadProgress } from "../UploadProgress";
import styles from "./Import.module.css";

export function Import() {
	const { t } = useTranslation();
	const navigate = useNavigate();
	const [phase, setPhase] = useState<ImportPhase>("idle");
	const [fileName, setFileName] = useState("");
	const [progress, setProgress] = useState(0);
	const [errorMessage, setErrorMessage] = useState("");

	const reset = () => {
		setPhase("idle");
		setFileName("");
		setProgress(0);
		setErrorMessage("");
	};

	const handleFileSelected = async (file: File) => {
		const validation = contentService.validatePdf(file);

		if (!validation.valid) {
			setErrorMessage(validation.error);
			setPhase("error");
			return;
		}

		setFileName(file.name);
		setProgress(0);
		setPhase("uploading");

		try {
			await contentService.simulateUpload({
				onProgress: setProgress,
			});

			const content = await contentService.importPdf(file);
			const job = await processingService.createProcessingJob(
				content.id,
				"summary",
			);

			navigate(`/processing/${job.id}`);
		} catch {
			setErrorMessage(t("workspace.import.couldNotStartProcessing"));
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<ImportHeader />
			<div className={styles.content}>
				{phase === "idle" ? (
					<PdfDropzone onFileSelected={handleFileSelected} />
				) : null}
				{phase === "uploading" ? (
					<UploadProgress fileName={fileName} progress={progress} />
				) : null}
				{phase === "error" ? (
					<UploadError message={errorMessage} onTryAgain={reset} />
				) : null}
			</div>
		</div>
	);
}
