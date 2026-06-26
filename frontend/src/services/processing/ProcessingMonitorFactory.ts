import { env } from "@/config/env";
import { PollingProcessingMonitor } from "./PollingProcessingMonitor";
import type { ProcessingMonitor } from "./ProcessingMonitor";
import type { ProcessingRepository } from "./ProcessingRepository";
import { SimulatedProcessingMonitor } from "./SimulatedProcessingMonitor";

export function createProcessingMonitor(
	repository: ProcessingRepository,
): ProcessingMonitor {
	if (env.useMock) {
		return new SimulatedProcessingMonitor(repository);
	}

	return new PollingProcessingMonitor(repository);
}
