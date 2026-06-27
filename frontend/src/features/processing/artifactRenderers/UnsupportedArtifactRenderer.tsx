import { Card } from "@/components/ui/Card";
import { SaveToLibraryAction } from "@/features/processing/SaveToLibrary";
import type { ArtifactRendererProps } from "./ArtifactRenderer";
import styles from "./UnsupportedArtifactRenderer.module.css";

export function UnsupportedArtifactRenderer({
	artifact,
	contentId,
}: ArtifactRendererProps) {
	if (artifact === null) {
		return null;
	}

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>{artifact.type}</p>
				<SaveToLibraryAction artifact={artifact} contentId={contentId} />
			</div>
			<p className={styles.message}>
				This artifact type is not yet supported in the viewer.
			</p>
			<p className={styles.content}>{artifact.content}</p>
		</Card>
	);
}
