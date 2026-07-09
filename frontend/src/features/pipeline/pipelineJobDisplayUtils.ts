import {
	formatDurationClock,
	formatProcessingSpeedRatio,
	resolveHardwareProfileDisplay,
} from "@/features/pipeline/pipelineLiveProgressUtils";
import type { PipelineJob } from "@/services/pipeline/jobTypes";

export function formatPipelineDateTime(
	value: string | null | undefined,
	locale: string,
): string | null {
	if (!value) {
		return null;
	}

	const parsed = Date.parse(value);

	if (Number.isNaN(parsed)) {
		return null;
	}

	return new Intl.DateTimeFormat(locale, {
		dateStyle: "short",
		timeStyle: "short",
	}).format(new Date(parsed));
}

export function estimateMinutesFromSeconds(
	seconds?: number | null,
): number | null {
	if (seconds == null || seconds <= 0) {
		return null;
	}

	return Math.ceil(seconds / 60);
}

export function formatAccuracyPercent(value?: number | null): string | null {
	if (value == null) {
		return null;
	}

	return `${Math.round(value)}%`;
}

export type PipelineTimingLabels = {
	startedAt: string;
	notStarted: string;
	estimatedDuration: string;
	estimatedCompletion: string;
	actualCompletion: string;
	actualDuration: string;
	estimationAccuracy: string;
	elapsedTime: string;
	remainingTime: string;
	engine: string;
	engineVersion: string;
	provider: string;
	hardwareProfile: string;
	currentStep: string;
	checkpoint: string;
	processingSpeed: string;
	currentSegment: string;
	audioProcessed: string;
	worker: string;
	dockerContainer: string;
	waitingForWorker: string;
	averageSpeed: string;
};

export function buildPipelineStageTimingLines(
	job: PipelineJob,
	labels: PipelineTimingLabels,
	locale: string,
): string[] {
	const lines: string[] = [];
	const startedLabel = formatPipelineDateTime(job.startedAt, locale);

	if (startedLabel) {
		lines.push(labels.startedAt.replace("{{time}}", startedLabel));
	} else if (job.status === "queued" || job.status === "waiting_user_choice") {
		lines.push(labels.notStarted);
	}

	const durationMinutes = estimateMinutesFromSeconds(
		job.estimatedDurationSeconds,
	);

	if (durationMinutes != null) {
		lines.push(
			labels.estimatedDuration.replace("{{minutes}}", String(durationMinutes)),
		);
	}

	if (job.status === "running" || job.status === "queued") {
		const elapsedClock = formatDurationClock(job.elapsedSeconds);

		if (elapsedClock) {
			lines.push(labels.elapsedTime.replace("{{time}}", elapsedClock));
		}

		const remainingClock = formatDurationClock(job.estimatedRemainingSeconds);

		if (remainingClock) {
			lines.push(labels.remainingTime.replace("{{time}}", remainingClock));
		}

		const estimatedCompletion = formatPipelineDateTime(
			job.estimatedCompletionAt,
			locale,
		);

		if (estimatedCompletion) {
			lines.push(
				labels.estimatedCompletion.replace("{{time}}", estimatedCompletion),
			);
		}

		const speed = formatProcessingSpeedRatio(job.processingSpeedRatio);

		if (speed) {
			lines.push(labels.processingSpeed.replace("{{speed}}", speed));
		}

		if (
			job.currentSegment != null &&
			job.totalSegments != null &&
			job.totalSegments > 0
		) {
			lines.push(
				labels.currentSegment
					.replace("{{current}}", String(job.currentSegment))
					.replace("{{total}}", String(job.totalSegments)),
			);
		}

		const audioProcessed = formatDurationClock(job.audioProcessedSeconds);
		const audioTotal = formatDurationClock(job.audioTotalSeconds);

		if (audioProcessed && audioTotal) {
			lines.push(
				labels.audioProcessed
					.replace("{{processed}}", audioProcessed)
					.replace("{{total}}", audioTotal),
			);
		}

		if (job.workerStatus === "waiting_for_update" || job.workerStale) {
			lines.push(labels.waitingForWorker);
		}
	}

	if (
		job.status === "completed" ||
		job.status === "waiting_user_confirmation" ||
		job.status === "failed" ||
		job.status === "cancelled"
	) {
		const actualCompletion = formatPipelineDateTime(job.completedAt, locale);

		if (actualCompletion) {
			lines.push(labels.actualCompletion.replace("{{time}}", actualCompletion));
		}

		const actualDuration = formatDurationClock(job.actualDurationSeconds);

		if (actualDuration) {
			lines.push(labels.actualDuration.replace("{{time}}", actualDuration));
		}

		const accuracy = formatAccuracyPercent(job.estimationAccuracyPercent);

		if (accuracy) {
			lines.push(labels.estimationAccuracy.replace("{{percent}}", accuracy));
		}

		const averageSpeed = formatProcessingSpeedRatio(job.processingSpeedRatio);

		if (averageSpeed) {
			lines.push(labels.averageSpeed.replace("{{speed}}", averageSpeed));
		}
	}

	if (job.engineId || job.currentEngine) {
		lines.push(
			labels.engine.replace(
				"{{engine}}",
				job.engineId ?? job.currentEngine ?? "unknown",
			),
		);
	}

	if (job.engineVersion) {
		lines.push(labels.engineVersion.replace("{{version}}", job.engineVersion));
	}

	if (job.provider) {
		lines.push(labels.provider.replace("{{provider}}", job.provider));
	}

	const hardwareProfile = resolveHardwareProfileDisplay(job);

	if (hardwareProfile) {
		lines.push(labels.hardwareProfile.replace("{{profile}}", hardwareProfile));
	}

	const stepLabel = job.checkpointLabel ?? job.currentStep;

	if (stepLabel && (job.status === "running" || job.status === "queued")) {
		lines.push(labels.currentStep.replace("{{step}}", stepLabel));
	}

	if (job.checkpoint && job.status === "running") {
		lines.push(labels.checkpoint.replace("{{checkpoint}}", job.checkpoint));
	}

	if (job.workerId) {
		lines.push(labels.worker.replace("{{worker}}", job.workerId));
	}

	if (job.dockerContainer) {
		lines.push(
			labels.dockerContainer.replace("{{container}}", job.dockerContainer),
		);
	}

	return lines;
}

export function resolvePipelineProgressLabel(
	job: PipelineJob,
): string | undefined {
	return job.checkpointLabel ?? job.currentStep ?? undefined;
}
