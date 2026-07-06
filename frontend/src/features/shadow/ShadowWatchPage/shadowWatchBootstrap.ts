import { appendActivityLog } from "@/features/activity/activityLogStore";
import type { VideoJobStatus } from "@/services/video/types";
import { ApiError } from "@/shared/errors/ApiError";
import { NetworkError } from "@/shared/errors/NetworkError";
import { formatApiErrorBody } from "@/shared/errors/formatApiErrorBody";
import type {
	BootstrapCheckItem,
	BootstrapLogEntry,
} from "./ShadowWatchBootstrapPanel";

const LOG_PREFIX = "[ShadowWatch]";

export function logBootstrap(
	message: string,
	level: BootstrapLogEntry["level"] = "info",
): void {
	const line = `${LOG_PREFIX} ${message}`;
	if (level === "error") {
		console.error(line);
		return;
	}

	if (level === "warn") {
		console.warn(line);
		return;
	}

	console.info(line);
}

export function formatBootstrapError(error: unknown): string {
	if (error instanceof ApiError) {
		const detail = formatApiErrorBody(error.body);
		return detail
			? `API ${error.status} — ${detail}`
			: `API ${error.status} — ${error.message}`;
	}

	if (error instanceof NetworkError) {
		const parts = [error.message];
		const cause = error.cause;

		if (cause instanceof Error && cause.message.trim() !== "") {
			parts.push(cause.message.trim());
		} else if (typeof cause === "string" && cause.trim() !== "") {
			parts.push(cause.trim());
		}

		return parts.join(" — ");
	}

	if (error instanceof Error) {
		return error.message;
	}

	return String(error);
}

type BootstrapTranslator = (
	key: string,
	params?: Record<string, string | number>,
) => string;

export function logVideoPipelineDiagnostics(
	pushLog: (
		message: string,
		level?: BootstrapLogEntry["level"],
	) => void,
	t: BootstrapTranslator,
	jobStatus: VideoJobStatus,
): void {
	pushLog(
		t("pipeline.shadow.bootstrapLogPipelineStatus", {
			status: jobStatus.status,
		}),
	);

	if (jobStatus.failedStage) {
		pushLog(
			t("pipeline.shadow.bootstrapLogFailedStage", {
				stage: jobStatus.failedStage,
			}),
			"warn",
		);
	}

	if (jobStatus.failureMessage) {
		const failureKey =
			jobStatus.status === "failed"
				? "pipeline.shadow.bootstrapLogFailureMessage"
				: "pipeline.shadow.bootstrapLogPreviousFailureMessage";
		pushLog(
			t(failureKey, {
				message: jobStatus.failureMessage,
			}),
			jobStatus.status === "failed" ? "error" : "warn",
		);
	}

	if (
		typeof jobStatus.lastProcessingDurationSeconds === "number" &&
		Number.isFinite(jobStatus.lastProcessingDurationSeconds)
	) {
		pushLog(
			t("pipeline.shadow.bootstrapLogProcessingDuration", {
				seconds: Math.round(jobStatus.lastProcessingDurationSeconds),
			}),
			"info",
		);
	}
}

export function formatPipelineFailureSummary(
	t: BootstrapTranslator,
	jobStatus: VideoJobStatus,
): string {
	if (jobStatus.failureMessage) {
		return t("pipeline.shadow.bootstrapPipelineFailedDetail", {
			stage: jobStatus.failedStage ?? t("pipeline.shadow.bootstrapUnknownStage"),
			message: jobStatus.failureMessage,
		});
	}

	return t("pipeline.shadow.bootstrapPipelineFailed");
}

export function logTranscriptUnavailableDiagnostics(
	pushLog: (
		message: string,
		level?: BootstrapLogEntry["level"],
	) => void,
	t: BootstrapTranslator,
	body: unknown,
): void {
	const detail = formatApiErrorBody(body);

	if (!detail) {
		pushLog(t("pipeline.shadow.bootstrapLogTranscriptMissing"), "warn");
		return;
	}

	pushLog(t("pipeline.shadow.bootstrapLogTranscriptMissing"), "warn");
	pushLog(
		t("pipeline.shadow.bootstrapLogTranscriptUnavailableDetail", {
			detail,
		}),
		"warn",
	);
}

export function appendBootstrapLog(
	entries: BootstrapLogEntry[],
	message: string,
	level: BootstrapLogEntry["level"] = "info",
): BootstrapLogEntry[] {
	logBootstrap(message, level);
	appendActivityLog(message, level, "ShadowWatch");
	const time = new Date().toLocaleTimeString();
	return [...entries.slice(-19), { time, message, level }];
}

export function updateBootstrapCheck(
	checks: BootstrapCheckItem[],
	id: string,
	patch: Partial<Omit<BootstrapCheckItem, "id">>,
): BootstrapCheckItem[] {
	return checks.map((item) => (item.id === id ? { ...item, ...patch } : item));
}
