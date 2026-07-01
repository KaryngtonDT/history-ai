import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
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

export function AudioPipelinePageLayout({
	stepId,
	children,
}: AudioPipelinePageLayoutProps) {
	const { t } = useTranslation();
	const { audioId = "" } = useParams();
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
			<div className={styles.content}>{children}</div>
		</div>
	);
}
