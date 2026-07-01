import { useRef } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./VideoDropzone.module.css";

interface VideoDropzoneProps {
	onFileSelected: (file: File) => void;
	disabled?: boolean;
}

export function VideoDropzone({
	onFileSelected,
	disabled,
}: VideoDropzoneProps) {
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
				aria-label={t("pipeline.upload.videoDropzoneAria")}
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
					title={t("pipeline.upload.videoDropTitle")}
					description={t("pipeline.upload.videoDropDescription")}
					action={
						<>
							<input
								ref={inputRef}
								type="file"
								accept="video/mp4,video/quicktime,video/x-matroska,.mp4,.mov,.mkv"
								className={styles.input}
								disabled={disabled}
								onChange={(event) => handleFile(event.target.files?.[0])}
							/>
							<Button
								variant="primary"
								disabled={disabled}
								onClick={() => inputRef.current?.click()}
							>
								{t("pipeline.upload.videoSelectCta")}
							</Button>
						</>
					}
				/>
			</fieldset>
		</Card>
	);
}
