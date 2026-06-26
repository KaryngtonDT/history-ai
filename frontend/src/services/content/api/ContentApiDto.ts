/** Shape returned by GET /api/contents (Symfony ListContentsResponse). */
export interface ContentApiDto {
	id: string;
	title: string;
	sourceType: string;
	status: string;
	createdAt: string;
	updatedAt: string;
}

/** Body accepted by POST /api/contents. */
export interface CreateContentApiDto {
	title: string;
	sourceType: string;
}

/** Body returned by POST /api/contents (201). */
export interface CreateContentApiResponseDto {
	id: string;
}
