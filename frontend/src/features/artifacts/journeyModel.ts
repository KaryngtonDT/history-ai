import type { VideoPipelineStepId } from "@/features/product/videoRoutes";

export type ArtifactStatus = "open" | "generate" | "locked" | "unknown";

export interface ArtifactJourneyStep {
	id: VideoPipelineStepId | "video" | "quality";
	label: string;
	description: string;
	status: ArtifactStatus;
	path?: string;
	dependsOnLabel?: string;
}

export function buildArtifactJourney(
	videoId: string | null,
): ArtifactJourneyStep[] {
	const base = videoId
		? [
				{
					id: "transcript" as const,
					label: "Transcript",
					description: "Speech-to-text output",
					status: "open" as const,
					path: `/video/${videoId}/transcript`,
				},
				{
					id: "translations" as const,
					label: "Translations",
					description: "Translated text",
					status: "open" as const,
					path: `/video/${videoId}/translations`,
					dependsOnLabel: "Transcript",
				},
				{
					id: "audio" as const,
					label: "Audio",
					description: "Generated speech",
					status: "open" as const,
					path: `/video/${videoId}/audio`,
					dependsOnLabel: "Translations",
				},
				{
					id: "voice-clone" as const,
					label: "Voice Clone",
					description: "Cloned speaker voice",
					status: "open" as const,
					path: `/video/${videoId}/voice-clone`,
					dependsOnLabel: "Audio",
				},
				{
					id: "lip-sync" as const,
					label: "Lip Sync",
					description: "Aligned video",
					status: "open" as const,
					path: `/video/${videoId}/lip-sync`,
					dependsOnLabel: "Voice Clone",
				},
				{
					id: "render" as const,
					label: "Final Render",
					description: "Downloadable MP4",
					status: "open" as const,
					path: `/video/${videoId}/render`,
					dependsOnLabel: "Lip Sync",
				},
			]
		: [];

	return [
		{
			id: "video",
			label: "Video",
			description: "Source upload",
			status: videoId ? "open" : "generate",
			path: videoId ? undefined : "/video/upload",
		},
		...base,
		{
			id: "quality",
			label: "Quality",
			description: "Publication readiness score",
			status: videoId ? "open" : "locked",
			path: videoId ? `/video/upload` : undefined,
			dependsOnLabel: "Final Render",
		},
	];
}
