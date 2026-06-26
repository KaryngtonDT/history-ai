import { useState } from "react";
import { contentService } from "@/services/content/ContentService";
import { ImportHeader } from "../ImportHeader";
import { PdfDropzone } from "../PdfDropzone";
import type { ImportPhase } from "../types";
import { UploadError } from "../UploadError";
import { UploadProgress } from "../UploadProgress";
import { UploadSuccess } from "../UploadSuccess";
import styles from "./Import.module.css";

const CREATE_CONTENT_ERROR =
	"Could not create content. Check that the backend is running and try again.";

export function Import() {
	const [phase, setPhase] = useState<ImportPhase>("idle");
	const [fileName, setFileName] = useState("");
	const [contentId, setContentId] = useState("");
	const [progress, setProgress] = useState(0);
	const [errorMessage, setErrorMessage] = useState("");

	const reset = () => {
		setPhase("idle");
		setFileName("");
		setContentId("");
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

			const result = await contentService.importPdf(file);
			setContentId(result.id);
			setPhase("success");
		} catch {
			setErrorMessage(CREATE_CONTENT_ERROR);
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
				{phase === "success" ? (
					<UploadSuccess
						fileName={fileName}
						contentId={contentId}
						onImportAnother={reset}
					/>
				) : null}
				{phase === "error" ? (
					<UploadError message={errorMessage} onTryAgain={reset} />
				) : null}
			</div>
		</div>
	);
}
