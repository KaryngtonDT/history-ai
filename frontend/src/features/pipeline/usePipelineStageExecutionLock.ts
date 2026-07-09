import { usePipelineSourceContext } from "@/features/pipeline/PipelineSourceContext";
import { isPipelineStageExecutionLocked } from "@/features/pipeline/pipelineJobStateUtils";

export function usePipelineStageExecutionLock(stage: string) {
	const { status, refresh } = usePipelineSourceContext();

	return {
		executionLocked: isPipelineStageExecutionLocked(status, stage),
		refreshPipeline: refresh,
		pipelineStatus: status,
	};
}
