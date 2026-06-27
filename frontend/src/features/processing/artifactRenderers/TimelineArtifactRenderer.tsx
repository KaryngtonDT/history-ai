import { useEffect, useState } from "react";
import type { Timeline } from "@/domain/timeline";
import { ProcessingTimelineArtifact } from "@/features/processing/ProcessingTimelineArtifact";
import { timelineService } from "@/services/timeline/TimelineService";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

type TimelineViewState =
	| { status: "loading" }
	| { status: "structured"; timeline: Timeline }
	| { status: "markdown" };

export function TimelineArtifactRenderer({
	artifact,
	contentId,
	readOnly = false,
}: ArtifactRendererProps) {
	const [viewState, setViewState] = useState<TimelineViewState>({
		status: "loading",
	});

	useEffect(() => {
		if (artifact === null) {
			return;
		}

		let cancelled = false;

		setViewState({ status: "loading" });

		timelineService
			.getTimeline(artifact.id)
			.then((timeline) => {
				if (cancelled) {
					return;
				}

				if (timeline === null) {
					setViewState({ status: "markdown" });
					return;
				}

				setViewState({ status: "structured", timeline });
			})
			.catch(() => {
				if (!cancelled) {
					setViewState({ status: "markdown" });
				}
			});

		return () => {
			cancelled = true;
		};
	}, [artifact]);

	const isLoading = artifact !== null && viewState.status === "loading";
	const structuredTimeline =
		viewState.status === "structured" ? viewState.timeline : undefined;

	return (
		<ProcessingTimelineArtifact
			artifact={artifact}
			contentId={readOnly ? undefined : contentId}
			isLoading={isLoading}
			structuredTimeline={structuredTimeline}
		/>
	);
}
