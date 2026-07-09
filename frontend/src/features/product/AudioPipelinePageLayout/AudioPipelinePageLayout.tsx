import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
import {
	PipelineProgressPanel,
	PipelineSourceProvider,
} from "@/features/pipeline";
import { useTranslation } from "@/i18n/useTranslation";
import {
	AUDIO_PIPELINE_STEPS,
	type AudioPipelineStepId,
	getAudioPipelineStepLabel,
} from "../audioRoutes";
import { PageIntroduction } from "../PageIntroduction";
import styles from "./AudioPipelinePageLayout.module.css";

interface AudioPipelinePageLayoutProps {
	stepId: AudioPipelineStepId;
	children: ReactNode;
}

function AudioPipelinePageLayoutInner({
	stepId,
	children,
	audioId,
}: AudioPipelinePageLayoutProps & { audioId: string }) {
	const { t } = useTranslation();
	const step = AUDIO_PIPELINE_STEPS.find((entry) => entry.id === stepId);
	const stepLabel = step
		? getAudioPipelineStepLabel(t, step.id)
		: t("pipeline.layouts.stepFallback");

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("pipeline.layouts.audioEyebrow")}
				title={stepLabel}
				description={t("pipeline.layouts.audioDescription", {
					step: stepLabel.toLowerCase(),
				})}
				whatCanIDo={t("pipeline.layouts.audioWhatCanIDo")}
			/>
			<ArtifactJourney
				videoId={audioId || null}
				title={t("pipeline.layouts.journeyProcessing")}
			/>
			<PipelineProgressPanel sourceId={audioId} />
			<div className={styles.content}>{children}</div>
		</div>
	);
}

export function AudioPipelinePageLayout({
	stepId,
	children,
}: AudioPipelinePageLayoutProps) {
	const { audioId = "" } = useParams();

	if (!audioId) {
		return (
			<AudioPipelinePageLayoutInner stepId={stepId} audioId="">
				{children}
			</AudioPipelinePageLayoutInner>
		);
	}

	return (
		<PipelineSourceProvider sourceId={audioId}>
			<AudioPipelinePageLayoutInner stepId={stepId} audioId={audioId}>
				{children}
			</AudioPipelinePageLayoutInner>
		</PipelineSourceProvider>
	);
}
