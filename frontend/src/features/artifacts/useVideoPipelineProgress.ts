import { useEffect, useState } from "react";
import { audioService } from "@/services/audio/AudioService";
import { lipSyncService } from "@/services/lipsync/LipSyncService";
import { videoRenderService } from "@/services/render/VideoRenderService";
import { transcriptService } from "@/services/transcript/TranscriptService";
import { translationService } from "@/services/translation/TranslationService";
import type { VideoStatus } from "@/services/video/types";
import { videoService } from "@/services/video/VideoService";
import { voiceCloneService } from "@/services/voice/VoiceCloneService";

export interface VideoPipelineProgress {
	loading: boolean;
	videoStatus: VideoStatus | null;
	hasTranscript: boolean;
	hasTranslations: boolean;
	hasAudio: boolean;
	hasVoiceClone: boolean;
	hasLipSync: boolean;
	hasRender: boolean;
}

const EMPTY_PROGRESS: VideoPipelineProgress = {
	loading: false,
	videoStatus: null,
	hasTranscript: false,
	hasTranslations: false,
	hasAudio: false,
	hasVoiceClone: false,
	hasLipSync: false,
	hasRender: false,
};

export function useVideoPipelineProgress(
	videoId: string | null,
	refreshKey = 0,
): VideoPipelineProgress {
	const [progress, setProgress] = useState<VideoPipelineProgress>({
		...EMPTY_PROGRESS,
		loading: Boolean(videoId),
	});

	useEffect(() => {
		void refreshKey;

		if (!videoId) {
			setProgress(EMPTY_PROGRESS);
			return;
		}

		let cancelled = false;

		async function load() {
			const id = videoId as string;
			setProgress((current) => ({ ...current, loading: true }));

			const [
				statusResult,
				transcriptResult,
				translationsResult,
				audioResult,
				voiceCloneResult,
				lipSyncResult,
				renderResult,
			] = await Promise.allSettled([
				videoService.getStatus(id),
				transcriptService.getTranscript(id),
				translationService.listTranslations(id),
				audioService.listAudio(id),
				voiceCloneService.listVoiceClones(id),
				lipSyncService.listLipSyncs(id),
				videoRenderService.listRenders(id),
			]);

			if (cancelled) {
				return;
			}

			setProgress({
				loading: false,
				videoStatus:
					statusResult.status === "fulfilled"
						? statusResult.value.status
						: null,
				hasTranscript:
					transcriptResult.status === "fulfilled" &&
					transcriptResult.value !== null,
				hasTranslations:
					translationsResult.status === "fulfilled" &&
					translationsResult.value.length > 0,
				hasAudio:
					audioResult.status === "fulfilled" && audioResult.value.length > 0,
				hasVoiceClone:
					voiceCloneResult.status === "fulfilled" &&
					voiceCloneResult.value.length > 0,
				hasLipSync:
					lipSyncResult.status === "fulfilled" &&
					lipSyncResult.value.length > 0,
				hasRender:
					renderResult.status === "fulfilled" && renderResult.value.length > 0,
			});
		}

		void load();

		return () => {
			cancelled = true;
		};
	}, [videoId, refreshKey]);

	return progress;
}
