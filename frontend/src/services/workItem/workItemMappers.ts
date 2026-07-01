import type { AudioSource } from "@/services/audioSource/types";
import type {
	Content,
	ContentSourceType,
} from "@/services/content/domain/Content";
import type { Project } from "@/services/workspace/types";
import type { YouTubeImport } from "@/services/youtubeSource/types";
import type { WorkItem, WorkItemStatus, WorkItemType } from "./types";

const SOURCE_TYPE_MAP: Record<ContentSourceType, WorkItemType> = {
	pdf: "pdf",
	audio: "audio",
	video: "video",
	youtube: "youtube",
};

const TYPE_ICONS: Record<WorkItemType, string> = {
	video: "🎥",
	pdf: "📄",
	audio: "🎤",
	youtube: "▶️",
	project: "📁",
};

function contentStatusToWorkItemStatus(
	status: Content["status"],
): WorkItemStatus {
	return status === "processing" ? "processing" : "completed";
}

function contentCurrentStep(
	sourceType: ContentSourceType,
	status: Content["status"],
): string {
	if (status === "completed") {
		if (sourceType === "pdf") {
			return "Knowledge graph ready";
		}

		if (sourceType === "audio") {
			return "Transcript ready";
		}

		if (sourceType === "youtube") {
			return "Processing complete";
		}

		return "Completed";
	}

	if (sourceType === "pdf") {
		return "Processing document";
	}

	if (sourceType === "audio") {
		return "Generating transcript";
	}

	return "Processing";
}

function contentOpenRoute(id: string, sourceType: ContentSourceType): string {
	if (sourceType === "pdf") {
		return `/processing/${id}`;
	}

	if (sourceType === "audio") {
		return `/audio/${id}`;
	}

	if (sourceType === "video") {
		return `/video/${id}`;
	}

	return `/library`;
}

function contentDescription(sourceType: ContentSourceType): string {
	switch (sourceType) {
		case "pdf":
			return "Document imported for knowledge extraction.";
		case "audio":
			return "Audio file queued for transcription.";
		case "video":
			return "Video ready for localization pipeline.";
		case "youtube":
			return "YouTube source linked for processing.";
		default:
			return "Content item in History AI.";
	}
}

function contentCapabilities(sourceType: ContentSourceType): string[] {
	switch (sourceType) {
		case "pdf":
			return ["processing", "library", "chat", "graph"];
		case "audio":
			return ["transcript", "library"];
		case "video":
			return ["transcript", "translation", "audio", "render"];
		case "youtube":
			return ["transcript", "library"];
		default:
			return ["library"];
	}
}

export function mapContentToWorkItem(content: Content): WorkItem {
	const type = SOURCE_TYPE_MAP[content.sourceType];
	const status = contentStatusToWorkItemStatus(content.status);
	const openRoute = contentOpenRoute(content.id, content.sourceType);

	return {
		id: content.id,
		type,
		title: content.title,
		status,
		progress: content.progress,
		currentStep: contentCurrentStep(content.sourceType, content.status),
		openRoute,
		primaryActionLabel: status === "processing" ? "Resume" : "Open",
		primaryActionRoute: openRoute,
		icon: TYPE_ICONS[type],
		description: contentDescription(content.sourceType),
		capabilities: contentCapabilities(content.sourceType),
		updatedAt: new Date().toISOString(),
	};
}

export function mapVideoToWorkItem(
	videoId: string,
	filename: string,
	addedAt: string,
	batchProgress: number,
	batchStatus: Project["batchStatus"],
): WorkItem {
	const isProcessing = batchStatus === "running" || batchStatus === "pending";

	return {
		id: videoId,
		type: "video",
		title: filename.replace(/\.[^.]+$/, ""),
		status: isProcessing ? "processing" : "ready",
		progress: isProcessing ? batchProgress : 100,
		currentStep: isProcessing ? "Batch localization" : "Ready for pipeline",
		openRoute: `/video/${videoId}`,
		primaryActionLabel: isProcessing ? "Resume" : "Open",
		primaryActionRoute: `/video/${videoId}`,
		icon: TYPE_ICONS.video,
		description: "Video in project workspace.",
		capabilities: [
			"transcript",
			"translation",
			"audio",
			"voice-clone",
			"lip-sync",
			"render",
		],
		updatedAt: addedAt,
	};
}

export function mapYoutubeToWorkItem(importItem: YouTubeImport): WorkItem {
	const isProcessing =
		importItem.videoStatus === "queued" ||
		importItem.videoStatus === "processing";

	return {
		id: importItem.videoId,
		type: "youtube",
		title: importItem.metadata.title,
		status: isProcessing ? "processing" : "ready",
		progress:
			importItem.videoStatus === "completed" ? 100 : isProcessing ? 35 : 0,
		currentStep: isProcessing ? "Importing from YouTube" : "Ready for pipeline",
		openRoute: `/video/${importItem.videoId}`,
		primaryActionLabel: "Open",
		primaryActionRoute: `/video/${importItem.videoId}`,
		icon: TYPE_ICONS.youtube,
		description: "YouTube video imported into History AI.",
		capabilities: [
			"transcript",
			"translation",
			"audio",
			"voice-clone",
			"lip-sync",
			"render",
		],
		updatedAt: importItem.importedAt,
	};
}

export function mapAudioSourceToWorkItem(source: AudioSource): WorkItem {
	const isProcessing =
		source.status === "queued" || source.status === "processing";

	return {
		id: source.id,
		type: "audio",
		title: source.title,
		status: isProcessing ? "processing" : "ready",
		progress: source.status === "completed" ? 100 : isProcessing ? 40 : 0,
		currentStep: isProcessing ? "Generating transcript" : "Transcript ready",
		openRoute: `/audio/${source.id}`,
		primaryActionLabel: isProcessing ? "Resume" : "Open",
		primaryActionRoute: `/audio/${source.id}`,
		icon: TYPE_ICONS.audio,
		description: "Audio source in History AI.",
		capabilities: ["transcript", "translation", "library", "chat", "graph"],
		updatedAt: source.createdAt,
	};
}

export function mapProjectToWorkItem(project: Project): WorkItem {
	const isProcessing =
		project.batchStatus === "running" || project.batchStatus === "pending";

	return {
		id: project.id,
		type: "project",
		title: project.name,
		status: isProcessing ? "processing" : "ready",
		progress: project.batchProgress,
		currentStep: isProcessing
			? `Batch processing (${project.videos.length} videos)`
			: `${project.videos.length} videos`,
		openRoute: "/workspace",
		primaryActionLabel: "Open workspace",
		primaryActionRoute: "/workspace",
		icon: TYPE_ICONS.project,
		description: "Project with videos and batch processing.",
		capabilities: ["batch", "team", "analytics", "reviews"],
		updatedAt: project.createdAt,
	};
}
