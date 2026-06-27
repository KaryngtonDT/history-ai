import { Card } from "@/components/ui/Card";
import { ArtifactCardHeader } from "./ArtifactCardHeader";
import type { ArtifactRendererProps } from "./ArtifactRenderer";
import styles from "./SummaryArtifactRenderer.module.css";

export function SummaryArtifactRenderer({
	artifact,
	contentId,
	readOnly = false,
}: ArtifactRendererProps) {
	if (artifact === null) {
		return null;
	}

	return (
		<Card className={styles.card}>
			<ArtifactCardHeader
				label="Summary"
				artifact={artifact}
				contentId={contentId}
				showSave={!readOnly}
			/>
			<p className={styles.content}>{artifact.content}</p>
		</Card>
	);
}
