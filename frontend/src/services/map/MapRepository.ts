import type { HistoricalPlace } from "./types";

export interface MapRepository {
	getTimelineMap(artifactId: string): Promise<HistoricalPlace[] | null>;
}
