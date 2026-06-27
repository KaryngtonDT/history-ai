import { ProcessingTimelineArtifact } from "@/features/processing/ProcessingTimelineArtifact";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function TimelineArtifactRenderer({
	artifact,
	contentId,
	readOnly = false,
}: ArtifactRendererProps) {
	return (
		<ProcessingTimelineArtifact
			artifact={artifact}
			contentId={readOnly ? undefined : contentId}
		/>
	);
}
