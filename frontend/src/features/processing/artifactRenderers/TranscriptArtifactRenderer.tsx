import { Card } from "@/components/ui/Card";
import type { ArtifactRendererProps } from "./ArtifactRenderer";
import styles from "./TranscriptArtifactRenderer.module.css";

export function TranscriptArtifactRenderer({
	artifact,
}: ArtifactRendererProps) {
	if (artifact === null) {
		return null;
	}

	return (
		<Card className={styles.card}>
			<p className={styles.label}>Transcript</p>
			<p className={styles.content}>{artifact.content}</p>
		</Card>
	);
}
