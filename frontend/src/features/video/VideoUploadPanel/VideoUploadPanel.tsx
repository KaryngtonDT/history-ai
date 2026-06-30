import { useCallback, useEffect, useState } from "react";
import { VideoIntelligenceDashboard } from "@/features/intelligence";
import { OptimizationDashboard } from "@/features/optimization";
import { ProcessingModeSelector } from "@/features/orchestrator";
import { ProcessingResourceMonitor } from "@/features/scheduler";
import type { VideoIntelligence } from "@/services/intelligence/types";
import { videoIntelligenceService } from "@/services/intelligence/VideoIntelligenceService";
import { optimizationService } from "@/services/optimization/OptimizationService";
import type { ExecutionOptimization } from "@/services/optimization/types";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type {
	PipelineRecommendation,
	ProcessingMode,
} from "@/services/orchestrator/types";
import { schedulerService } from "@/services/scheduler/SchedulerService";
import type { ExecutionSchedule } from "@/services/scheduler/types";
import { videoService } from "@/services/video/VideoService";
import { ValidationError } from "@/shared/errors";
import type { VideoUploadPhase } from "../types";
import { VideoDropzone } from "../VideoDropzone";
import { VideoUploadError } from "../VideoUploadError";
import { VideoUploadHeader } from "../VideoUploadHeader";
import { VideoUploadProgress } from "../VideoUploadProgress";
import { VideoUploadSuccess } from "../VideoUploadSuccess";
import styles from "./VideoUploadPanel.module.css";

const UPLOAD_FLOW_ERROR =
	"Could not upload the video. Check that the backend is running and try again.";

export function VideoUploadPanel() {
	const [phase, setPhase] = useState<VideoUploadPhase>("idle");
	const [fileName, setFileName] = useState("");
	const [progress, setProgress] = useState(0);
	const [errorMessage, setErrorMessage] = useState("");
	const [videoId, setVideoId] = useState("");
	const [status, setStatus] = useState("");
	const [processingMode, setProcessingMode] =
		useState<ProcessingMode>("automatic");
	const [recommendation, setRecommendation] =
		useState<PipelineRecommendation | null>(null);
	const [intelligence, setIntelligence] = useState<VideoIntelligence | null>(
		null,
	);
	const [optimization, setOptimization] =
		useState<ExecutionOptimization | null>(null);
	const [schedule, setSchedule] = useState<ExecutionSchedule | null>(null);
	const [loadingAutomaticPreview, setLoadingAutomaticPreview] = useState(false);

	const loadAutomaticPreview = useCallback(async () => {
		if (!orchestratorService.isAutomaticMode(processingMode)) {
			setRecommendation(null);
			setIntelligence(null);
			setOptimization(null);
			setSchedule(null);
			return;
		}

		setLoadingAutomaticPreview(true);

		try {
			const [
				recommendationResult,
				intelligenceResult,
				optimizationResult,
				scheduleResult,
			] = await Promise.all([
				orchestratorService.loadRecommendation(),
				videoIntelligenceService.loadPreviewIntelligence(),
				optimizationService.loadPreviewOptimization(),
				schedulerService.loadPreviewSchedule(),
			]);
			setRecommendation(recommendationResult);
			setIntelligence(intelligenceResult);
			setOptimization(optimizationResult);
			setSchedule(scheduleResult);
		} finally {
			setLoadingAutomaticPreview(false);
		}
	}, [processingMode]);

	useEffect(() => {
		void loadAutomaticPreview();
	}, [loadAutomaticPreview]);

	const reset = () => {
		setPhase("idle");
		setFileName("");
		setProgress(0);
		setErrorMessage("");
		setVideoId("");
		setStatus("");
	};

	const handleFileSelected = async (file: File) => {
		const validation = videoService.validateVideo(file);

		if (!validation.valid) {
			setErrorMessage(validation.error);
			setPhase("error");
			return;
		}

		setFileName(file.name);
		setProgress(0);
		setPhase("uploading");

		try {
			const result = await videoService.uploadVideo(file, {
				onProgress: setProgress,
				processingMode,
				strategy: recommendation?.strategy,
			});

			setVideoId(result.videoId);
			setStatus(result.status);
			setPhase("success");
		} catch (error) {
			if (error instanceof ValidationError) {
				setErrorMessage(error.message);
			} else {
				setErrorMessage(UPLOAD_FLOW_ERROR);
			}
			setPhase("error");
		}
	};

	return (
		<div className={styles.root}>
			<VideoUploadHeader />
			<div className={styles.content}>
				{phase === "idle" ? (
					<>
						<ProcessingModeSelector
							mode={processingMode}
							onChange={setProcessingMode}
						/>
						{processingMode === "automatic" ? (
							<>
								<VideoIntelligenceDashboard
									intelligence={intelligence}
									recommendation={recommendation}
									loading={loadingAutomaticPreview}
								/>
								<OptimizationDashboard
									optimization={optimization}
									loading={loadingAutomaticPreview}
								/>
								<ProcessingResourceMonitor
									schedule={schedule}
									loading={loadingAutomaticPreview}
								/>
							</>
						) : null}
						<VideoDropzone onFileSelected={handleFileSelected} />
					</>
				) : null}
				{phase === "uploading" ? (
					<VideoUploadProgress fileName={fileName} progress={progress} />
				) : null}
				{phase === "success" ? (
					<VideoUploadSuccess
						fileName={fileName}
						videoId={videoId}
						status={status}
						onUploadAnother={reset}
					/>
				) : null}
				{phase === "error" ? (
					<VideoUploadError message={errorMessage} onTryAgain={reset} />
				) : null}
			</div>
		</div>
	);
}
