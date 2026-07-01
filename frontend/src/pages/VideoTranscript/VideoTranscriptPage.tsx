import { VideoPipelinePageLayout } from "@/features/product";
import { TranscriptPanel } from "@/features/transcript";

export function VideoTranscriptPage() {
	return (
		<VideoPipelinePageLayout stepId="transcript" featureId="transcript">
			<TranscriptPanel />
		</VideoPipelinePageLayout>
	);
}
