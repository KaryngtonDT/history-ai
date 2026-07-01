import { useRef } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
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

	const handleFile = (file: File | undefined) => {
		if (file) {
			onFileSelected(file);
		}
	};

	return (
		<Card className={styles.card}>
			<fieldset
				className={styles.dropzone}
				aria-label="Audio drop zone"
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
					title="Drop your audio here"
					description="Or select MP3, WAV, FLAC, M4A, or OGG from your device."
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
								Select audio
							</Button>
						</>
					}
				/>
			</fieldset>
		</Card>
	);
}
