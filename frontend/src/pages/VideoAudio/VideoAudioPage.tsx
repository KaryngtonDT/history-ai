import { AudioPlayerPanel } from "@/features/audio";
import { VideoPipelinePageLayout } from "@/features/product";

export function VideoAudioPage() {
	return (
		<VideoPipelinePageLayout stepId="audio" featureId="audio">
			<AudioPlayerPanel />
		</VideoPipelinePageLayout>
	);
}
