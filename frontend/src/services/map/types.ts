export interface Coordinates {
	latitude: number;
	longitude: number;
}

export interface HistoricalPlace {
	name: string;
	coordinates: Coordinates;
	description: string | null;
}

export interface CoordinatesApiDto {
	latitude: number;
	longitude: number;
}

export interface HistoricalPlaceApiDto {
	name: string;
	coordinates: CoordinatesApiDto;
	description: string | null;
}

export interface TimelineMapApiDto {
	places: HistoricalPlaceApiDto[];
}

export function mapHistoricalPlaceFromApi(
	dto: HistoricalPlaceApiDto,
): HistoricalPlace {
	return {
		name: dto.name,
		coordinates: {
			latitude: dto.coordinates.latitude,
			longitude: dto.coordinates.longitude,
		},
		description: dto.description,
	};
}

export function mapTimelineMapFromApi(
	dto: TimelineMapApiDto,
): HistoricalPlace[] {
	return dto.places.map(mapHistoricalPlaceFromApi);
}
