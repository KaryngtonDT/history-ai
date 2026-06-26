import { useState } from "react";
import { importService } from "@/services/import/ImportService";
import type { ImportPhase } from "@/services/import/types";
import { ImportHeader } from "../ImportHeader";
import { PdfDropzone } from "../PdfDropzone";
import { UploadError } from "../UploadError";
import { UploadProgress } from "../UploadProgress";
import { UploadSuccess } from "../UploadSuccess";
import styles from "./Import.module.css";

export function Import() {
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
		const validation = importService.validatePdf(file);

		if (!validation.valid) {
			setErrorMessage(validation.error);
			setPhase("error");
			return;
		}

		setFileName(file.name);
		setProgress(0);
		setPhase("uploading");

		await importService.simulateUpload({
			onProgress: setProgress,
		});

		setPhase("success");
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
					<UploadSuccess fileName={fileName} onImportAnother={reset} />
				) : null}
				{phase === "error" ? (
					<UploadError message={errorMessage} onTryAgain={reset} />
				) : null}
			</div>
		</div>
	);
}
