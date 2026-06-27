import { Card } from "@/components/ui/Card";
import type { ArtifactRendererProps } from "./ArtifactRenderer";
import styles from "./UnsupportedArtifactRenderer.module.css";

export function UnsupportedArtifactRenderer({
	artifact,
}: ArtifactRendererProps) {
	if (artifact === null) {
		return null;
	}

	return (
		<Card className={styles.card}>
			<p className={styles.label}>{artifact.type}</p>
			<p className={styles.message}>
				This artifact type is not yet supported in the viewer.
			</p>
			<p className={styles.content}>{artifact.content}</p>
		</Card>
	);
}
