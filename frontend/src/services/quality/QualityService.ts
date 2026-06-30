import type { QualityRepository } from "./QualityRepository";
import { createQualityRepository } from "./QualityRepositoryFactory";
import type { QualityMetric, QualityReport } from "./types";
import {
	PUBLICATION_RECOMMENDATION_LABELS,
	QUALITY_CATEGORY_LABELS,
} from "./types";

export class QualityService {
	private readonly repository: QualityRepository;

	constructor(repository: QualityRepository) {
		this.repository = repository;
	}

	loadPreviewQuality(): Promise<QualityReport> {
		return this.repository.getPreviewQuality();
	}

	loadByVideoId(videoId: string): Promise<QualityReport> {
		return this.repository.getByVideoId(videoId);
	}

	formatCategory(category: string): string {
		return QUALITY_CATEGORY_LABELS[category] ?? category;
	}

	formatRecommendation(recommendation: string): string {
		return PUBLICATION_RECOMMENDATION_LABELS[recommendation] ?? recommendation;
	}

	isReadyForPublishing(recommendation: string): boolean {
		return recommendation === "ready";
	}

	needsReview(recommendation: string): boolean {
		return recommendation === "review_recommended";
	}

	needsRegeneration(recommendation: string): boolean {
		return recommendation === "regenerate_required";
	}

	sortedMetrics(metrics: QualityMetric[]): QualityMetric[] {
		const order = [
			"audio",
			"translation",
			"voice_clone",
			"lip_sync",
			"rendering",
		];

		return [...metrics].sort(
			(a, b) => order.indexOf(a.category) - order.indexOf(b.category),
		);
	}
}

export const qualityService = new QualityService(createQualityRepository());
