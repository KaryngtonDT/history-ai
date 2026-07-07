import { useState } from "react";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { TranscriptSourceChoiceDialog } from "./PipelineProgressPanel";
import { usePipelineChoiceState } from "./usePipelineChoiceState";
import { useTranslation } from "@/i18n/useTranslation";
import { formatApiErrorBody } from "@/shared/errors/formatApiErrorBody";
import { ApiError } from "@/shared/errors/ApiError";

export function PipelineTranscriptChoicePanel({
	sourceId,
	onChoiceSubmitted,
}: {
	sourceId: string;
	onChoiceSubmitted?: () => void;
}) {
	const { t } = useTranslation();
	const { waitingChoiceJobs, refresh } = usePipelineChoiceState(sourceId);
	const [submitting, setSubmitting] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const submitChoice = async (choice: "youtube_transcript" | "local_engine") => {
		setSubmitting(true);
		setError(null);

		try {
			await pipelineJobService.submitChoice(sourceId, "speech_to_text", choice);
			await refresh();
			onChoiceSubmitted?.();
		} catch (submitError) {
			const message =
				submitError instanceof ApiError
					? formatApiErrorBody(submitError.body) || submitError.message
					: submitError instanceof Error
						? submitError.message
						: t("pipeline.progress.choiceFailed");
			setError(message);
		} finally {
			setSubmitting(false);
		}
	};

	return (
		<TranscriptSourceChoiceDialog
			open={waitingChoiceJobs.length > 0}
			submitting={submitting}
			error={error}
			onChooseYoutube={() => void submitChoice("youtube_transcript")}
			onChooseLocal={() => void submitChoice("local_engine")}
		/>
	);
}
