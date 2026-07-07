import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { TranscriptSourceChoiceDialog } from "./PipelineProgressPanel";
import { usePipelineChoiceState } from "./usePipelineChoiceState";

export function PipelineTranscriptChoicePanel({
	sourceId,
	onChoiceSubmitted,
}: {
	sourceId: string;
	onChoiceSubmitted?: () => void;
}) {
	const { waitingChoiceJobs, refresh } = usePipelineChoiceState(sourceId);

	const handleYoutube = () => {
		void pipelineJobService
			.submitChoice(sourceId, "speech_to_text", "youtube_transcript")
			.then(() => refresh())
			.then(() => onChoiceSubmitted?.());
	};

	const handleLocal = () => {
		void pipelineJobService
			.submitChoice(sourceId, "speech_to_text", "local_engine")
			.then(() => refresh())
			.then(() => onChoiceSubmitted?.());
	};

	return (
		<TranscriptSourceChoiceDialog
			open={waitingChoiceJobs.length > 0}
			onChooseYoutube={handleYoutube}
			onChooseLocal={handleLocal}
		/>
	);
}
