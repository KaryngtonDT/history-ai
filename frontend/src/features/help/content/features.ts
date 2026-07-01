export type FeatureHelpId =
	| "video-upload"
	| "transcript"
	| "translation"
	| "audio"
	| "voice-clone"
	| "lip-sync"
	| "final-render"
	| "quality"
	| "pipeline"
	| "automatic-mode"
	| "ai-engines"
	| "optimization"
	| "scheduler"
	| "history"
	| "reprocess"
	| "reviews"
	| "preferences"
	| "collaboration"
	| "analytics"
	| "workspace";

export interface FeatureHelpContent {
	id: FeatureHelpId;
	title: string;
	short: string;
	details: string;
	bestPractice: string;
	commonMistake: string;
	nextStep: string;
	readingMinutes: number;
	faq: Array<{ question: string; answer: string }>;
}

export const FEATURE_HELP: Record<FeatureHelpId, FeatureHelpContent> = {
	"video-upload": {
		id: "video-upload",
		title: "Video Upload",
		short: "Upload your source video to start the AI localization pipeline.",
		details:
			"Supported formats include MP4, MOV, and MKV. After upload, History AI queues transcription, translation, and optional audio, voice, lip-sync, and render steps.",
		bestPractice:
			"Use clear audio and stable lighting. Choose Automatic mode to let the AI Director recommend the best pipeline.",
		commonMistake:
			"Uploading very long videos without checking workspace batch limits.",
		nextStep: "Open the transcript after processing begins.",
		readingMinutes: 1,
		faq: [
			{
				question: "Manual vs Automatic mode?",
				answer:
					"Automatic lets the AI Director choose engines and previews intelligence. Manual runs only what you configure.",
			},
		],
	},
	transcript: {
		id: "transcript",
		title: "Transcript",
		short: "The transcript is the text extracted from your video audio.",
		details:
			"Speech-to-text (Faster-Whisper) converts spoken words into timed segments. Every downstream step depends on this artifact.",
		bestPractice: "Review transcript accuracy before translating.",
		commonMistake: "Skipping transcript review when audio is noisy.",
		nextStep: "Generate translations for your target languages.",
		readingMinutes: 1,
		faq: [],
	},
	translation: {
		id: "translation",
		title: "Translation",
		short: "Translations adapt your transcript into other languages.",
		details:
			"The translation engine (e.g. Ollama) produces per-language text used for audio generation.",
		bestPractice: "Select only the languages you need to reduce cost and time.",
		commonMistake: "Translating before fixing transcript errors.",
		nextStep: "Generate audio for translated languages.",
		readingMinutes: 2,
		faq: [],
	},
	audio: {
		id: "audio",
		title: "Audio Generation",
		short: "Text-to-speech creates spoken audio from translations.",
		details:
			"TTS engines like F5-TTS synthesize voice audio per language. Preview before voice cloning.",
		bestPractice: "Preview generic audio before investing in voice clone.",
		commonMistake: "Cloning voice from low-quality generic audio.",
		nextStep: "Generate voice clone or proceed to lip sync.",
		readingMinutes: 2,
		faq: [],
	},
	"voice-clone": {
		id: "voice-clone",
		title: "Voice Clone",
		short:
			"Voice clone makes translated audio sound like the original speaker.",
		details:
			"OpenVoice analyzes the source voice and applies it to generated speech. Best for interviews, podcasts, and education.",
		bestPractice: "Use when speaker identity matters to your audience.",
		commonMistake: "Using voice clone on very noisy source audio.",
		nextStep: "Run lip sync to align video mouth movements.",
		readingMinutes: 2,
		faq: [],
	},
	"lip-sync": {
		id: "lip-sync",
		title: "Lip Sync",
		short: "Lip sync aligns mouth movements with the new audio track.",
		details:
			"LatentSync processes video frames so lip movements match translated speech.",
		bestPractice: "Ensure face visibility is good in the source video.",
		commonMistake: "Running lip sync before audio quality is acceptable.",
		nextStep: "Render the final MP4.",
		readingMinutes: 2,
		faq: [],
	},
	"final-render": {
		id: "final-render",
		title: "Final Render",
		short: "The final render produces a downloadable MP4.",
		details:
			"FFmpeg combines video, audio, and lip-sync output into the published file.",
		bestPractice: "Check quality score before publishing.",
		commonMistake: "Downloading before reviewing quality recommendations.",
		nextStep: "Review quality and save to workspace history.",
		readingMinutes: 1,
		faq: [],
	},
	quality: {
		id: "quality",
		title: "Quality Score",
		short: "Quality scores estimate how ready your video is for publication.",
		details:
			"Scores cover translation, voice, lip sync, and rendering. Recommendations suggest review or regeneration.",
		bestPractice: "Aim for 90+ before publishing externally.",
		commonMistake: "Ignoring category-specific low scores.",
		nextStep: "Leave a review to improve future recommendations.",
		readingMinutes: 2,
		faq: [],
	},
	pipeline: {
		id: "pipeline",
		title: "Pipeline Configuration",
		short: "Choose which AI engine runs at each processing stage.",
		details:
			"Configure speech-to-text, translation, TTS, voice clone, lip sync, and render providers.",
		bestPractice: "Match engines to your content type and quality needs.",
		commonMistake: "Changing pipeline mid-batch without reprocessing.",
		nextStep: "Upload a video or run workspace batch.",
		readingMinutes: 3,
		faq: [],
	},
	"automatic-mode": {
		id: "automatic-mode",
		title: "Automatic Mode",
		short: "Automatic mode lets the AI Director choose the best pipeline.",
		details:
			"Video intelligence analyzes speakers, audio, and visuals to recommend engines and parameters.",
		bestPractice: "Use automatic mode for first-time users.",
		commonMistake:
			"Using manual mode without understanding stage dependencies.",
		nextStep: "Review the recommendation explanation on upload.",
		readingMinutes: 2,
		faq: [],
	},
	"ai-engines": {
		id: "ai-engines",
		title: "AI Engines",
		short: "View registered AI providers and their capabilities.",
		details:
			"Each engine supports specific stages: STT, translation, TTS, voice clone, lip sync, render.",
		bestPractice: "Compare providers in analytics after several runs.",
		commonMistake: "Assuming all engines support all languages.",
		nextStep: "Configure pipeline stage assignments.",
		readingMinutes: 2,
		faq: [],
	},
	optimization: {
		id: "optimization",
		title: "Execution Optimization",
		short: "Optimization tunes engine parameters based on video intelligence.",
		details:
			"The AI Director adjusts beam sizes, quality presets, and stage parameters automatically.",
		bestPractice: "Review optimization explanations before batch runs.",
		commonMistake: "Ignoring optimization warnings on long videos.",
		nextStep: "Check scheduler resource plan.",
		readingMinutes: 2,
		faq: [],
	},
	scheduler: {
		id: "scheduler",
		title: "Scheduler",
		short: "The scheduler plans CPU, GPU, and IO usage across pipeline stages.",
		details:
			"Resource queues show which stages run in parallel and estimated completion time.",
		bestPractice: "Monitor GPU queue on large batch jobs.",
		commonMistake: "Starting many batches without checking resource usage.",
		nextStep: "View workspace analytics after processing.",
		readingMinutes: 2,
		faq: [],
	},
	history: {
		id: "history",
		title: "Execution History",
		short: "History tracks every render version of a video.",
		details:
			"Compare versions, inspect pipeline configuration used, and see quality per run.",
		bestPractice: "Compare versions before choosing which to publish.",
		commonMistake: "Reprocessing without reviewing what changed.",
		nextStep: "Reprocess from a previous version if needed.",
		readingMinutes: 2,
		faq: [],
	},
	reprocess: {
		id: "reprocess",
		title: "Reprocess",
		short: "Reprocess reruns the pipeline from a previous history version.",
		details:
			"Uses the saved pipeline configuration from that version as a starting point.",
		bestPractice: "Reprocess after changing only one pipeline stage.",
		commonMistake: "Reprocessing without noting which provider failed.",
		nextStep: "Compare new version with previous in history.",
		readingMinutes: 1,
		faq: [],
	},
	reviews: {
		id: "reviews",
		title: "Reviews",
		short:
			"Reviews capture human feedback on translation, voice, and render quality.",
		details:
			"Star ratings and comments feed the adaptive AI Director preferences.",
		bestPractice: "Review at least overall and translation categories.",
		commonMistake: "Skipping reviews on acceptable-but-not-perfect output.",
		nextStep: "Check your preference profile summary.",
		readingMinutes: 1,
		faq: [],
	},
	preferences: {
		id: "preferences",
		title: "Preferences",
		short: "Your preference profile adapts future AI recommendations.",
		details:
			"Built from reviews: translation style, voice stability, rendering preset, lip sync strength.",
		bestPractice: "Leave several reviews to build a reliable profile.",
		commonMistake: "Expecting preferences to change without review input.",
		nextStep: "Run next batch in automatic mode.",
		readingMinutes: 1,
		faq: [],
	},
	collaboration: {
		id: "collaboration",
		title: "Team Collaboration",
		short: "Invite teammates with roles: Owner, Editor, Reviewer, Viewer.",
		details:
			"Shared workspaces let teams manage projects, batch jobs, and reviews together.",
		bestPractice: "Assign Reviewer role to quality gatekeepers.",
		commonMistake: "Giving Editor access without onboarding on pipeline order.",
		nextStep: "Create a project and invite your team.",
		readingMinutes: 2,
		faq: [],
	},
	analytics: {
		id: "analytics",
		title: "Workspace Analytics",
		short: "Analytics show processing performance, provider usage, and errors.",
		details:
			"Telemetry from Sprint 49 aggregates duration, success rate, GPU usage, and top providers.",
		bestPractice: "Check analytics weekly to optimize provider choices.",
		commonMistake: "Ignoring repeated errors in recent failures list.",
		nextStep: "Adjust pipeline providers based on statistics.",
		readingMinutes: 2,
		faq: [],
	},
	workspace: {
		id: "workspace",
		title: "Workspace",
		short: "Organize videos into projects and run batch processing.",
		details:
			"Workspace combines projects, batch progress, team, reviews, history, and analytics.",
		bestPractice: "One project per campaign or course.",
		commonMistake: "Batch processing without videos in the project.",
		nextStep: "Upload videos or link existing uploads to the project.",
		readingMinutes: 2,
		faq: [],
	},
};

export function getFeatureHelp(id: FeatureHelpId): FeatureHelpContent {
	return FEATURE_HELP[id];
}
