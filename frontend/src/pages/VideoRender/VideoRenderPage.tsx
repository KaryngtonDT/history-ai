import { VideoPipelinePageLayout } from "@/features/product";
import { FinalVideoPanel } from "@/features/render";

export function VideoRenderPage() {
	return (
		<VideoPipelinePageLayout stepId="render" featureId="final-render">
			<FinalVideoPanel />
		</VideoPipelinePageLayout>
	);
}
