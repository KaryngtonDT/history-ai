import { AudioPipelinePageLayout } from "@/features/product/AudioPipelinePageLayout";
import { TranslationPanel } from "@/features/translation";

export function AudioTranslationsPage() {
	return (
		<AudioPipelinePageLayout stepId="translations">
			<TranslationPanel />
		</AudioPipelinePageLayout>
	);
}
