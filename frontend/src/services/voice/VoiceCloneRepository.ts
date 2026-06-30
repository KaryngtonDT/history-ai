import type { TranslationLanguage } from "@/services/translation/types";
import type {
	GenerateVoiceCloneRequest,
	VideoVoiceClone,
	VideoVoiceCloneSummary,
} from "./types";

export interface VoiceCloneRepository {
	listVoiceClones(videoId: string): Promise<VideoVoiceCloneSummary[]>;
	getVoiceClone(
		videoId: string,
		language: TranslationLanguage,
	): Promise<VideoVoiceClone | null>;
	generateVoiceClone(
		videoId: string,
		request: GenerateVoiceCloneRequest,
	): Promise<void>;
}
