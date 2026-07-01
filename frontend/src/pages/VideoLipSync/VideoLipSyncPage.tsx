import { LipSyncPanel } from "@/features/lipsync";
import { VideoPipelinePageLayout } from "@/features/product";

export function VideoLipSyncPage() {
	return (
		<VideoPipelinePageLayout stepId="lip-sync" featureId="lip-sync">
			<LipSyncPanel />
		</VideoPipelinePageLayout>
	);
}
