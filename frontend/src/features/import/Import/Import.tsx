import { useState } from "react";
import { useNavigate } from "react-router";
import { CollapsibleSection } from "@/components/ui/CollapsibleSection";
import { CompactPageIntroduction, CreatePageLayout } from "@/features/product";
import { useTranslation } from "@/i18n";
import { contentService } from "@/services/content/ContentService";
import { processingService } from "@/services/processing/ProcessingService";
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
			<CompactPageIntroduction
				eyebrow={t("workspace.import.eyebrow")}
				title={t("workspace.import.headerTitle")}
				description={t("workspace.import.headerDescription")}
				whatCanIDo={t("workspace.import.whatCanIDo")}
			/>
			<div className={styles.content}>
				{phase === "idle" ? (
					<CreatePageLayout
						primary={<PdfDropzone onFileSelected={handleFileSelected} />}
						secondary={
							<>
								<CollapsibleSection
									title={t("pipeline.create.whatHappensNext")}
								>
									<p className={styles.guidanceText}>
										{t("workspace.import.whatHappensNext")}
									</p>
								</CollapsibleSection>
								<CollapsibleSection title={t("workspace.import.helpTitle")}>
									<p className={styles.guidanceText}>
										{t("workspace.import.supportedFiles")}
									</p>
									<p className={styles.guidanceText}>
										{t("workspace.import.whatCanIDo")}
									</p>
								</CollapsibleSection>
							</>
						}
					/>
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
