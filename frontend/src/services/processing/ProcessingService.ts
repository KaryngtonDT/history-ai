import type {
	ProcessingErrorCallback,
	ProcessingMonitor,
	ProcessingUpdateCallback,
} from "./ProcessingMonitor";
import { createProcessingMonitor } from "./ProcessingMonitorFactory";
import type {
	CreateProcessingJobResult,
	ProcessingJobType,
	ProcessingRepository,
} from "./ProcessingRepository";
import { createProcessingRepository } from "./ProcessingRepositoryFactory";
import type { ProcessingData } from "./types";

export class ProcessingService {
	private readonly repository: ProcessingRepository;
	private readonly monitor: ProcessingMonitor;

	constructor(repository: ProcessingRepository, monitor: ProcessingMonitor) {
		this.repository = repository;
		this.monitor = monitor;
	}

	getProcessing(id: string): Promise<ProcessingData | null> {
		return this.repository.getProcessing(id);
	}

	createProcessingJob(
		contentId: string,
		type: ProcessingJobType = "summary",
	): Promise<CreateProcessingJobResult> {
		return this.repository.createProcessingJob(contentId, type);
	}

	subscribeToProcessing(
		jobId: string,
		onUpdate: ProcessingUpdateCallback,
		onError?: ProcessingErrorCallback,
	): () => void {
		return this.monitor.subscribe(jobId, onUpdate, onError);
	}
}

const repository = createProcessingRepository();

export const processingService = new ProcessingService(
	repository,
	createProcessingMonitor(repository),
);
