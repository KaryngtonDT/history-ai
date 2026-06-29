import { useRef } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
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

	const handleFile = (file: File | undefined) => {
		if (file) {
			onFileSelected(file);
		}
	};

	return (
		<Card className={styles.card}>
			<fieldset
				className={styles.dropzone}
				aria-label="Video drop zone"
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
					title="Drop your video here"
					description="Or select MP4, MOV, or MKV from your device."
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
								Select video
							</Button>
						</>
					}
				/>
			</fieldset>
		</Card>
	);
}
