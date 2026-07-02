export const shadowTeachingEn = {
	shadowTeaching: {
		eyebrow: "Shadow",
		title: "Teaching",
		description:
			"Track Shadow's teaching plan for your current learning stage: missions, lessons, exercises, and revisions.",
		whatCanIDo:
			"Review your learning path, inspect current lesson priorities, manage voice mode, and reset the teaching plan.",
		tabs: {
			teaching: "Teaching",
		},
		learningPath: {
			title: "Learning Path",
		},
		currentLesson: {
			title: "Current Lesson",
			exercisesDue: "Exercises due: {{count}}",
			revisionDue: "Revision due: {{count}}",
		},
		objectives: {
			title: "Objectives",
		},
		exercises: {
			title: "Exercises",
		},
		revisionQueue: {
			title: "Revision Queue",
		},
		progress: {
			title: "Progress",
			objectives: "Objectives",
			exercises: "Exercises",
			checkpoints: "Checkpoints",
		},
		history: {
			title: "History",
		},
		preferences: {
			title: "Preferences",
			voiceMode: "Voice mode",
			saved: "Teaching preferences saved.",
			modes: {
				coach: "Coach",
				mentor: "Mentor",
				story: "Story",
			},
		},
		panel: {
			title: "Teaching Companion",
			todayLesson: "Today's lesson",
			nextCheckpoint: "Next checkpoint",
			noneCheckpoint: "No checkpoint pending",
			exercisesCount: "Exercises pending",
			revisionReminder: "Revision reminder",
		},
		reset: {
			action: "Reset teaching",
			success: "Teaching plan reset.",
		},
		empty: {
			currentLesson: "No lesson selected yet.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Could not load teaching plan.",
			preferencesFailed: "Could not update teaching preferences.",
			resetFailed: "Could not reset teaching plan.",
		},
	},
} as const;
