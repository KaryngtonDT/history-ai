import { ExplainThisButton } from "@/features/help";
import { PageIntroduction } from "@/features/product";
import { VideoUploadPanel } from "@/features/video";

export function VideoUploadPage() {
	return (
		<section>
			<PageIntroduction
				eyebrow="Create"
				title="Upload Video"
				description="Start the AI localization pipeline by uploading your source video."
				whatCanIDo="Choose manual or automatic mode, upload a video, then follow the pipeline from transcript to final render."
				secondaryActions={<ExplainThisButton featureId="video-upload" />}
			/>
			<VideoUploadPanel />
		</section>
	);
}
