import { PIPELINE_PATH, pipelineResetPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { PipelineRepository } from "./PipelineRepository";
import type { PipelineConfiguration, PipelineStage } from "./types";
import { mapPipelineStageType } from "./types";

interface PipelineConfigurationApiDto {
	id: string;
	version: number;
	createdAt: string;
	updatedAt: string;
	stages: Array<{ stage: string; providerId: string }>;
}

function mapFromApi(dto: PipelineConfigurationApiDto): PipelineConfiguration {
	return {
		id: dto.id,
		version: dto.version,
		createdAt: dto.createdAt,
		updatedAt: dto.updatedAt,
		stages: dto.stages.map((stage) => ({
			stage: mapPipelineStageType(stage.stage),
			providerId: stage.providerId,
		})),
	};
}

export class HttpPipelineRepository implements PipelineRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async loadConfiguration(): Promise<PipelineConfiguration> {
		const response =
			await this.httpClient.get<PipelineConfigurationApiDto>(PIPELINE_PATH);

		return mapFromApi(response);
	}

	async saveConfiguration(
		stages: PipelineStage[],
	): Promise<PipelineConfiguration> {
		const response = await this.httpClient.put<PipelineConfigurationApiDto>(
			PIPELINE_PATH,
			{ stages },
		);

		return mapFromApi(response);
	}

	async resetConfiguration(): Promise<PipelineConfiguration> {
		const response = await this.httpClient.post<PipelineConfigurationApiDto>(
			pipelineResetPath(),
			{},
		);

		return mapFromApi(response);
	}
}
