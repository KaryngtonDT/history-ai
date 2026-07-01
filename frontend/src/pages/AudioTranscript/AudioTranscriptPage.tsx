import { AudioPipelinePageLayout } from "@/features/product/AudioPipelinePageLayout";
import { TranscriptPanel } from "@/features/transcript";

export function AudioTranscriptPage() {
	return (
		<AudioPipelinePageLayout stepId="transcript">
			<TranscriptPanel />
		</AudioPipelinePageLayout>
	);
}
