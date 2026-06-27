import type { MapRepository } from "./MapRepository";
import { createMapRepository } from "./MapRepositoryFactory";
import type { HistoricalPlace } from "./types";

const ARTIFACT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class MapService {
	private readonly repository: MapRepository;

	constructor(repository: MapRepository) {
		this.repository = repository;
	}

	getTimelineMap(artifactId: string): Promise<HistoricalPlace[] | null> {
		const normalized = artifactId.trim();

		if (normalized === "" || !ARTIFACT_ID_PATTERN.test(normalized)) {
			return Promise.resolve(null);
		}

		return this.repository.getTimelineMap(normalized);
	}
}

export const mapService = new MapService(createMapRepository());
