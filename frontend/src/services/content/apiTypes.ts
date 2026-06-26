/** Shape returned by GET /api/contents (Symfony ListContentsResponse). */
export interface ContentApiItem {
	id: string;
	title: string;
	sourceType: string;
	status: string;
	createdAt: string;
	updatedAt: string;
}

/** Body accepted by POST /api/contents. */
export interface CreateContentApiPayload {
	title: string;
	sourceType: string;
}

/** Body returned by POST /api/contents (201). */
export interface CreateContentApiResponse {
	id: string;
}
