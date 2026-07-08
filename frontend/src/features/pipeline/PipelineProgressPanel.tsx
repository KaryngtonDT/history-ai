import { useCallback, useEffect, useState } from "react";
import { createPortal } from "react-dom";
import { useTranslation } from "@/i18n/useTranslation";
import type {
	PipelineJob,
	PipelineSourceStatus,
} from "@/services/pipeline/jobTypes";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import {
	StageNotification,
	StageProgressBar,
	StageStatusBadge,
	StaleArtifactWarning,
} from "./PipelineComponents";
import styles from "./PipelineComponents.module.css";
import {
	isPipelineWaitingForTranscriptChoice,
	resolveJobsWaitingUserChoice,
} from "./pipelineChoiceUtils";
import { buildPipelineStageTimingLines } from "./pipelineJobDisplayUtils";

function PipelineStageCard({
	job,
	onContinue,
	onCancel,
	onRestart,
	continueLabel,
}: {
	job: PipelineJob;
	onContinue?: (job: PipelineJob) => void;
	onCancel?: (job: PipelineJob) => void;
	onRestart?: (job: PipelineJob) => void;
	continueLabel?: string;
}) {
	const { t, locale } = useTranslation();
	const timingLines = buildPipelineStageTimingLines(
		job,
		{
			startedAt: t("pipeline.progress.startedAt"),
			notStarted: t("pipeline.progress.notStarted"),
			estimatedDuration: t("pipeline.progress.estimatedDuration"),
			remainingMinutes: t("pipeline.progress.remainingMinutes"),
		},
		locale,
	);

	return (
		<div className={styles.stageCard}>
			<div className={styles.stageTitle}>
				{job.stage.replaceAll("_", " ")}{" "}
				<StageStatusBadge status={job.status} />
			</div>
			<StageProgressBar
				progressPercent={job.progressPercent}
				label={job.currentStep ?? undefined}
			/>
			{timingLines.map((line) => (
				<p key={line} className={styles.safeMessage}>
					{line}
				</p>
			))}
			{job.failureReason ? (
				<StageNotification title="Stage failed" message={job.failureReason} />
			) : null}
			<div className={styles.actions}>
				{job.status === "waiting_user_confirmation" && onContinue ? (
					<button
						type="button"
						className={styles.buttonPrimary}
						onClick={() => onContinue(job)}
					>
						{continueLabel ?? t("pipeline.progress.continue")}
					</button>
				) : null}
				{(job.status === "running" || job.status === "queued") && onCancel ? (
					<button
						type="button"
						className={styles.button}
						onClick={() => onCancel(job)}
					>
						{t("pipeline.progress.cancel")}
					</button>
				) : null}
				{onRestart ? (
					<button
						type="button"
						className={styles.button}
						onClick={() => onRestart(job)}
					>
						{t("pipeline.progress.restart")}
					</button>
				) : null}
			</div>
		</div>
	);
}

export function TranscriptSourceChoiceDialog({
	open,
	onChooseYoutube,
	onChooseLocal,
	submitting = false,
	error = null,
}: {
	open: boolean;
	onChooseYoutube: () => void;
	onChooseLocal: () => void;
	submitting?: boolean;
	error?: string | null;
}) {
	const { t } = useTranslation();

	if (!open) {
		return null;
	}

	const dialog = (
		<div
			className={styles.dialogBackdrop}
			role="dialog"
			aria-modal="true"
			aria-labelledby="transcript-choice-title"
		>
			<div className={styles.dialog}>
				<h3 id="transcript-choice-title">
					{t("pipeline.progress.youtubeChoiceTitle")}
				</h3>
				<p>{t("pipeline.progress.youtubeChoiceDescription")}</p>
				{error ? <p className={styles.choiceError}>{error}</p> : null}
				<div className={styles.dialogActions}>
					<button
						type="button"
						className={styles.buttonPrimary}
						onClick={onChooseYoutube}
						disabled={submitting}
					>
						{submitting
							? t("pipeline.progress.submittingChoice")
							: t("pipeline.progress.useYoutubeTranscript")}
					</button>
					<button
						type="button"
						className={styles.button}
						onClick={onChooseLocal}
						disabled={submitting}
					>
						{submitting
							? t("pipeline.progress.submittingChoice")
							: t("pipeline.progress.runLocalEngine")}
					</button>
				</div>
			</div>
		</div>
	);

	return createPortal(dialog, document.body);
}

export function PipelineProgressPanel({
	sourceId,
	pollMs = 5000,
	onStatusChange,
	hideChoiceDialog = false,
}: {
	sourceId: string;
	pollMs?: number;
	onStatusChange?: (status: PipelineSourceStatus) => void;
	hideChoiceDialog?: boolean;
}) {
	const { t } = useTranslation();
	const [status, setStatus] = useState<PipelineSourceStatus | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [choiceNotice, setChoiceNotice] = useState<string | null>(null);

	const refresh = useCallback(async () => {
		try {
			const next = await pipelineJobService.loadStatus(sourceId);
			setStatus(next);
			setError(null);
			onStatusChange?.(next);
		} catch {
			setError(t("pipeline.progress.loadFailed"));
		}
	}, [onStatusChange, sourceId, t]);

	useEffect(() => {
		void refresh();
	}, [refresh]);

	useEffect(() => {
		if (isPipelineWaitingForTranscriptChoice(status)) {
			return;
		}

		const timer = window.setInterval(() => {
			void refresh();
		}, pollMs);

		return () => window.clearInterval(timer);
	}, [pollMs, refresh, status]);

	const handleContinue = async (job: PipelineJob) => {
		await pipelineJobService.continueStage(sourceId, job.stage);
		await refresh();
	};

	const handleCancel = async (job: PipelineJob) => {
		await pipelineJobService.cancelStage(sourceId, job.stage);
		await refresh();
	};

	const handleRestart = async (job: PipelineJob) => {
		const confirmed = window.confirm(t("pipeline.progress.restartConfirm"));

		if (!confirmed) {
			return;
		}

		await pipelineJobService.startStage(sourceId, job.stage, true);
		await refresh();
	};

	const waitingChoice = status ? resolveJobsWaitingUserChoice(status) : [];
	const waitingConfirmation = status?.jobsWaitingConfirmation ?? [];
	const activeJobs = (status?.activeJobs ?? []).filter(
		(job) => !waitingChoice.some((choiceJob) => choiceJob.jobId === job.jobId),
	);
	const visibleJobs = [
		...activeJobs,
		...waitingConfirmation,
		...(status?.failedJobs ?? []),
	];

	const handleYoutubeChoice = async () => {
		try {
			await pipelineJobService.submitChoice(
				sourceId,
				"speech_to_text",
				"youtube_transcript",
			);
			setChoiceNotice(t("pipeline.progress.transcriptReadyNotice"));
			await refresh();
		} catch {
			setError(t("pipeline.progress.choiceFailed"));
		}
	};

	const handleLocalChoice = async () => {
		try {
			await pipelineJobService.submitChoice(
				sourceId,
				"speech_to_text",
				"local_engine",
			);
			await refresh();
		} catch {
			setError(t("pipeline.progress.choiceFailed"));
		}
	};

	return (
		<div className={styles.root} data-testid="pipeline-progress-panel">
			<div className={styles.header}>
				<h3>{t("pipeline.progress.title")}</h3>
				<p className={styles.safeMessage}>
					{t("pipeline.progress.refreshSafe")}
				</p>
				{status?.message ? (
					<StageNotification title="Pipeline" message={status.message} />
				) : null}
				{choiceNotice ? (
					<StageNotification
						title={t("pipeline.progress.title")}
						message={choiceNotice}
					/>
				) : null}
				{error ? <StageNotification title="Error" message={error} /> : null}
			</div>
			<StaleArtifactWarning artifactIds={status?.staleArtifacts ?? []} />
			{visibleJobs.map((job) => (
				<PipelineStageCard
					key={job.jobId}
					job={job}
					onContinue={handleContinue}
					onCancel={handleCancel}
					onRestart={handleRestart}
					continueLabel={
						job.stage === "speech_to_text"
							? t("pipeline.progress.continueToTranslation")
							: undefined
					}
				/>
			))}
			<TranscriptSourceChoiceDialog
				open={!hideChoiceDialog && waitingChoice.length > 0}
				onChooseYoutube={() => {
					void handleYoutubeChoice();
				}}
				onChooseLocal={() => {
					void handleLocalChoice();
				}}
			/>
		</div>
	);
}

export * from "./PipelineComponents";
