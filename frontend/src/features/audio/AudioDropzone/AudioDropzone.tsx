import { useRef } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./AudioDropzone.module.css";

interface AudioDropzoneProps {
	onFileSelected: (file: File) => void;
	disabled?: boolean;
}

export function AudioDropzone({
	onFileSelected,
	disabled,
}: AudioDropzoneProps) {
	const inputRef = useRef<HTMLInputElement>(null);
	const { t } = useTranslation();

	const handleFile = (file: File | undefined) => {
		if (file) {
			onFileSelected(file);
		}
	};

	return (
		<Card className={styles.card}>
			<fieldset
				className={styles.dropzone}
				aria-label={t("pipeline.upload.audioDropzoneAria")}
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
					title={t("pipeline.upload.audioDropTitle")}
					description={t("pipeline.upload.audioDropDescription")}
					action={
						<>
							<input
								ref={inputRef}
								type="file"
								accept="audio/mpeg,audio/wav,audio/flac,audio/mp4,audio/ogg,.mp3,.wav,.flac,.m4a,.ogg"
								className={styles.input}
								disabled={disabled}
								onChange={(event) => handleFile(event.target.files?.[0])}
							/>
							<Button
								variant="primary"
								disabled={disabled}
								onClick={() => inputRef.current?.click()}
							>
								{t("pipeline.upload.audioSelectCta")}
							</Button>
						</>
					}
				/>
			</fieldset>
		</Card>
	);
}
