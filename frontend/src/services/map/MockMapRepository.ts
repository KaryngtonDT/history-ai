import { parseTimeline } from "@/domain/timeline";
import { artifactMocksByContentId } from "@/mock/artifact";
import type { MapRepository } from "./MapRepository";
import type { Coordinates, HistoricalPlace } from "./types";

const PLACE_COORDINATES: Record<string, Coordinates> = {
	Rome: { latitude: 41.9028, longitude: 12.4964 },
	Carthage: { latitude: 36.8529, longitude: 10.3233 },
	Athens: { latitude: 37.9838, longitude: 23.7275 },
	Alexandria: { latitude: 31.2001, longitude: 29.9187 },
};

export class MockMapRepository implements MapRepository {
	async getTimelineMap(artifactId: string): Promise<HistoricalPlace[] | null> {
		for (const artifacts of Object.values(artifactMocksByContentId)) {
			const artifact = artifacts.find(
				(candidate) =>
					candidate.id === artifactId && candidate.type === "timeline",
			);

			if (artifact !== undefined) {
				return resolvePlacesFromTimeline(parseTimeline(artifact.content));
			}
		}

		return null;
	}
}

function resolvePlacesFromTimeline(
	timeline: ReturnType<typeof parseTimeline>,
): HistoricalPlace[] {
	const places: HistoricalPlace[] = [];
	const seen = new Set<string>();

	for (const section of timeline.sections) {
		for (const event of section.events) {
			for (const [name, coordinates] of Object.entries(PLACE_COORDINATES)) {
				if (seen.has(name)) {
					continue;
				}

				const pattern = new RegExp(`\\b${name}\\b`, "i");

				if (pattern.test(event.text)) {
					seen.add(name);
					places.push({
						name,
						coordinates,
						description: event.text,
					});
				}
			}
		}
	}

	return places;
}
