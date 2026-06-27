import { SaveToLibraryAction } from "@/features/processing/SaveToLibrary";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ArtifactCardHeader.module.css";

interface ArtifactCardHeaderProps {
	label: string;
	artifact: Artifact;
	contentId: string;
}

export function ArtifactCardHeader({
	label,
	artifact,
	contentId,
}: ArtifactCardHeaderProps) {
	return (
		<div className={styles.header}>
			<p className={styles.label}>{label}</p>
			<SaveToLibraryAction artifact={artifact} contentId={contentId} />
		</div>
	);
}
