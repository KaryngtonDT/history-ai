import { useState } from "react";
import { useNavigate } from "react-router";
import { contentService } from "@/services/content/ContentService";
import { ImportHeader } from "../ImportHeader";
import { PdfDropzone } from "../PdfDropzone";
import type { ImportPhase } from "../types";
import { UploadError } from "../UploadError";
import { UploadProgress } from "../UploadProgress";
import { UploadSuccess } from "../UploadSuccess";
import styles from "./Import.module.css";

export function Import() {
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

		await contentService.simulateUpload({
			onProgress: setProgress,
		});

		navigate("/processing/1");
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
