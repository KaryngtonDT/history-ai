import { VideoPipelinePageLayout } from "@/features/product";
import { VoiceClonePanel } from "@/features/voice";

export function VideoVoiceClonePage() {
	return (
		<VideoPipelinePageLayout stepId="voice-clone" featureId="voice-clone">
			<VoiceClonePanel />
		</VideoPipelinePageLayout>
	);
}
