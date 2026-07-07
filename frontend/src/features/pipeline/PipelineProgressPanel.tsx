import { useCallback, useEffect, useState } from "react";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import type { PipelineJob, PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./PipelineComponents.module.css";
import {
	StageNotification,
	StageProgressBar,
	StageStatusBadge,
	StaleArtifactWarning,
} from "./PipelineComponents";

function formatRemaining(seconds?: number | null): string | null {
	if (seconds == null || seconds <= 0) {
		return null;
	}

	const minutes = Math.ceil(seconds / 60);

	return `${minutes} min remaining (estimated)`;
}

function PipelineStageCard({
	job,
	onContinue,
	onCancel,
	onRestart,
}: {
	job: PipelineJob;
	onContinue?: (job: PipelineJob) => void;
	onCancel?: (job: PipelineJob) => void;
	onRestart?: (job: PipelineJob) => void;
}) {
	const { t } = useTranslation();
	const remaining = formatRemaining(job.estimatedRemainingSeconds);

	return (
		<div className={styles.stageCard}>
			<div className={styles.stageTitle}>
				{job.stage.replaceAll("_", " ")} <StageStatusBadge status={job.status} />
			</div>
			<StageProgressBar
				progressPercent={job.progressPercent}
				label={job.currentStep ?? undefined}
			/>
			{remaining ? <p className={styles.safeMessage}>{remaining}</p> : null}
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
						{t("pipeline.progress.continue")}
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
}: {
	open: boolean;
	onChooseYoutube: () => void;
	onChooseLocal: () => void;
}) {
	const { t } = useTranslation();

	if (!open) {
		return null;
	}

	return (
		<div className={styles.dialogBackdrop}>
			<div className={styles.dialog}>
				<h3>{t("pipeline.progress.youtubeChoiceTitle")}</h3>
				<p>{t("pipeline.progress.youtubeChoiceDescription")}</p>
				<div className={styles.dialogActions}>
					<button type="button" className={styles.buttonPrimary} onClick={onChooseYoutube}>
						{t("pipeline.progress.useYoutubeTranscript")}
					</button>
					<button type="button" className={styles.button} onClick={onChooseLocal}>
						{t("pipeline.progress.runLocalEngine")}
					</button>
				</div>
			</div>
		</div>
	);
}

export function PipelineProgressPanel({
	sourceId,
	pollMs = 5000,
	onStatusChange,
}: {
	sourceId: string;
	pollMs?: number;
	onStatusChange?: (status: PipelineSourceStatus) => void;
}) {
	const { t } = useTranslation();
	const [status, setStatus] = useState<PipelineSourceStatus | null>(null);
	const [error, setError] = useState<string | null>(null);

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
		const timer = window.setInterval(() => {
			void refresh();
		}, pollMs);

		return () => window.clearInterval(timer);
	}, [pollMs, refresh]);

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

	const waitingChoice = status?.jobsWaitingUserChoice ?? [];
	const visibleJobs = [
		...(status?.activeJobs ?? []),
		...(status?.jobsWaitingConfirmation ?? []),
		...(status?.failedJobs ?? []),
	];

	return (
		<div className={styles.root}>
			<div className={styles.header}>
				<h3>{t("pipeline.progress.title")}</h3>
				<p className={styles.safeMessage}>{t("pipeline.progress.refreshSafe")}</p>
				{status?.message ? <StageNotification title="Pipeline" message={status.message} /> : null}
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
				/>
			))}
			<TranscriptSourceChoiceDialog
				open={waitingChoice.length > 0}
				onChooseYoutube={() => {
					void pipelineJobService
						.submitChoice(sourceId, "speech_to_text", "youtube_transcript")
						.then(refresh);
				}}
				onChooseLocal={() => {
					void pipelineJobService
						.submitChoice(sourceId, "speech_to_text", "local_engine")
						.then(refresh);
				}}
			/>
		</div>
	);
}

export * from "./PipelineComponents";
