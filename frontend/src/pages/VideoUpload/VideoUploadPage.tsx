import { ExplainThisButton } from "@/features/help";
import { CompactPageIntroduction } from "@/features/product";
import { VideoUploadPanel } from "@/features/video";
import { useTranslation } from "@/i18n";

export function VideoUploadPage() {
	const { t } = useTranslation();

	return (
		<section>
			<CompactPageIntroduction
				eyebrow={t("pipeline.create.videoEyebrow")}
				title={t("pipeline.create.videoTitle")}
				description={t("pipeline.create.videoDescription")}
				whatCanIDo={t("pipeline.create.videoWhatCanIDo")}
				secondaryActions={<ExplainThisButton featureId="video-upload" />}
			/>
			<VideoUploadPanel />
		</section>
	);
}
