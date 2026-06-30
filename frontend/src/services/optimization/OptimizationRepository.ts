import type { ExecutionOptimization } from "./types";

export interface OptimizationRepository {
	getPreviewOptimization(): Promise<ExecutionOptimization>;
	getByVideoId(videoId: string): Promise<ExecutionOptimization>;
}
