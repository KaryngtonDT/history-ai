import {
	isTerminalProcessingStatus,
	type ProcessingMonitor,
	type ProcessingUpdateCallback,
} from "./ProcessingMonitor";
import type { ProcessingRepository } from "./ProcessingRepository";

const DEFAULT_POLL_INTERVAL_MS = 2000;

export class PollingProcessingMonitor implements ProcessingMonitor {
	private readonly repository: ProcessingRepository;
	private readonly pollIntervalMs: number;

	constructor(
		repository: ProcessingRepository,
		pollIntervalMs = DEFAULT_POLL_INTERVAL_MS,
	) {
		this.repository = repository;
		this.pollIntervalMs = pollIntervalMs;
	}

	subscribe(
		jobId: string,
		onUpdate: ProcessingUpdateCallback,
		onError?: (error: unknown) => void,
	): () => void {
		let active = true;
		let timerId: ReturnType<typeof setInterval> | null = null;

		const stop = (): void => {
			active = false;
			if (timerId !== null) {
				clearInterval(timerId);
				timerId = null;
			}
		};

		const poll = async (): Promise<void> => {
			if (!active) {
				return;
			}

			try {
				const data = await this.repository.getProcessing(jobId);

				if (!active) {
					return;
				}

				if (!data) {
					stop();
					return;
				}

				onUpdate(data);

				if (isTerminalProcessingStatus(data.status)) {
					stop();
				}
			} catch (error) {
				if (!active) {
					return;
				}

				onError?.(error);
				stop();
			}
		};

		void poll();
		timerId = setInterval(() => {
			void poll();
		}, this.pollIntervalMs);

		return stop;
	}
}
