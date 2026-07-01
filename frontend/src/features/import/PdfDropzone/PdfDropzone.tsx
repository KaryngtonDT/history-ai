import { useRef } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import styles from "./PdfDropzone.module.css";

interface PdfDropzoneProps {
	onFileSelected: (file: File) => void;
	disabled?: boolean;
}

export function PdfDropzone({ onFileSelected, disabled }: PdfDropzoneProps) {
	const { t } = useTranslation();
	const inputRef = useRef<HTMLInputElement>(null);

	const handleFile = (file: File | undefined) => {
		if (file) {
			onFileSelected(file);
		}
	};

	return (
		<Card className={styles.card}>
			<fieldset
				className={styles.dropzone}
				aria-label={t("workspace.import.pdfDropzoneAria")}
				disabled={disabled}
				onDragOver={(event) => {
					event.preventDefault();
				}}
				onDrop={(event) => {
					event.preventDefault();
					if (disabled) {
						return;
					}
					handleFile(event.dataTransfer.files[0]);
				}}
			>
				<EmptyState
					title={t("workspace.import.dropPdfTitle")}
					description={t("workspace.import.dropPdfDescription")}
					action={
						<>
							<input
								ref={inputRef}
								type="file"
								accept="application/pdf,.pdf"
								className={styles.input}
								disabled={disabled}
								onChange={(event) => handleFile(event.target.files?.[0])}
							/>
							<Button
								variant="primary"
								disabled={disabled}
								onClick={() => inputRef.current?.click()}
							>
								{t("workspace.import.selectPdf")}
							</Button>
						</>
					}
				/>
			</fieldset>
		</Card>
	);
}
