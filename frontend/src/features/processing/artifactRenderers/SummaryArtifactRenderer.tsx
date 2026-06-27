import { Card } from "@/components/ui/Card";
import type { ArtifactRendererProps } from "./ArtifactRenderer";
import styles from "./SummaryArtifactRenderer.module.css";

export function SummaryArtifactRenderer({ artifact }: ArtifactRendererProps) {
	if (artifact === null) {
		return null;
	}

	return (
		<Card className={styles.card}>
			<p className={styles.label}>Summary</p>
			<p className={styles.content}>{artifact.content}</p>
		</Card>
	);
}
