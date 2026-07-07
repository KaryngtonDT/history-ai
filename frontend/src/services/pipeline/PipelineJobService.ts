import {
	pipelineJobEventsPath,
	pipelineJobStageCancelPath,
	pipelineJobStageChoicePath,
	pipelineJobStageContinuePath,
	pipelineJobStageStartPath,
	pipelineJobsPath,
} from "@/config/api";
import { API_BASE_URL } from "@/config/api";
import { HttpClient } from "@/services/http/HttpClient";
import type {
	PipelineJob,
	PipelineNotification,
	PipelineSourceStatus,
	TranscriptUserChoice,
} from "./jobTypes";

export class PipelineJobService {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	loadStatus(sourceId: string): Promise<PipelineSourceStatus> {
		return this.httpClient.get<PipelineSourceStatus>(
			pipelineJobsPath(sourceId),
		);
	}

	loadEvents(sourceId: string): Promise<{
		sourceId: string;
		events: PipelineNotification[];
		status: PipelineSourceStatus;
	}> {
		return this.httpClient.get(pipelineJobEventsPath(sourceId));
	}

	startStage(
		sourceId: string,
		stage: string,
		forceRestart = false,
	): Promise<PipelineJob> {
		return this.httpClient.post<PipelineJob>(
			pipelineJobStageStartPath(sourceId, stage),
			{ forceRestart },
		);
	}

	cancelStage(
		sourceId: string,
		stage: string,
		reason?: string,
	): Promise<PipelineJob> {
		return this.httpClient.post<PipelineJob>(
			pipelineJobStageCancelPath(sourceId, stage),
			{ reason },
		);
	}

	continueStage(
		sourceId: string,
		stage: string,
	): Promise<{
		confirmedJob: PipelineJob;
		nextJob: PipelineJob | null;
	}> {
		return this.httpClient.post(
			pipelineJobStageContinuePath(sourceId, stage),
			{},
		);
	}

	submitChoice(
		sourceId: string,
		stage: string,
		choice: TranscriptUserChoice,
	): Promise<PipelineJob> {
		return this.httpClient.post<PipelineJob>(
			pipelineJobStageChoicePath(sourceId, stage),
			{ choice },
		);
	}
}

export const pipelineJobService = new PipelineJobService(
	new HttpClient(API_BASE_URL),
);
