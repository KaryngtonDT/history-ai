import { SaveToLibraryAction } from "@/features/processing/SaveToLibrary";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ArtifactCardHeader.module.css";

interface ArtifactCardHeaderProps {
	label: string;
	artifact: Artifact;
	contentId: string;
	showSave?: boolean;
}

export function ArtifactCardHeader({
	label,
	artifact,
	contentId,
	showSave = true,
}: ArtifactCardHeaderProps) {
	return (
		<div className={styles.header}>
			<p className={styles.label}>{label}</p>
			{showSave ? (
				<SaveToLibraryAction artifact={artifact} contentId={contentId} />
			) : null}
		</div>
	);
}
