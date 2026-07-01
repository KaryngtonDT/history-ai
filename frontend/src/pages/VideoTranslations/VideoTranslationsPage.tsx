import { VideoPipelinePageLayout } from "@/features/product";
import { TranslationPanel } from "@/features/translation";

export function VideoTranslationsPage() {
	return (
		<VideoPipelinePageLayout stepId="translations" featureId="translation">
			<TranslationPanel />
		</VideoPipelinePageLayout>
	);
}
