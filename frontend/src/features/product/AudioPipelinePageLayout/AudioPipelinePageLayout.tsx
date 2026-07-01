import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
import { AUDIO_PIPELINE_STEPS, type AudioPipelineStepId } from "../audioRoutes";
import { PageIntroduction } from "../PageIntroduction";
import styles from "./AudioPipelinePageLayout.module.css";

interface AudioPipelinePageLayoutProps {
	stepId: AudioPipelineStepId;
	children: ReactNode;
}

export function AudioPipelinePageLayout({
	stepId,
	children,
}: AudioPipelinePageLayoutProps) {
	const { audioId = "" } = useParams();
	const step = AUDIO_PIPELINE_STEPS.find((entry) => entry.id === stepId);

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Audio pipeline"
				title={step?.label ?? "Pipeline step"}
				description={`Work on ${step?.label?.toLowerCase() ?? "this step"} for your audio source.`}
				whatCanIDo="Review generated output and continue to the next pipeline step from the overview."
			/>
			<ArtifactJourney videoId={audioId || null} title="Processing progress" />
			<div className={styles.content}>{children}</div>
		</div>
	);
}
