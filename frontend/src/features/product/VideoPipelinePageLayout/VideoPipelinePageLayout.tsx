import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
import { ExplainThisButton } from "@/features/help";
import { PipelineProgressPanel } from "@/features/pipeline";
import type { FeatureHelpId } from "@/features/help/content/features";
import { getFeatureHelp } from "@/features/help/content/features";
import { useTranslation } from "@/i18n/useTranslation";
import { PageIntroduction } from "../PageIntroduction";
import {
	getVideoPipelineStepDescription,
	getVideoPipelineStepLabel,
	VIDEO_PIPELINE_STEPS,
	type VideoPipelineStepId,
} from "../videoRoutes";
import styles from "./VideoPipelinePageLayout.module.css";

interface VideoPipelinePageLayoutProps {
	stepId: VideoPipelineStepId;
	featureId: FeatureHelpId;
	children: ReactNode;
}

export function VideoPipelinePageLayout({
	stepId,
	featureId,
	children,
}: VideoPipelinePageLayoutProps) {
	const { t } = useTranslation();
	const { videoId = "" } = useParams();
	const step = VIDEO_PIPELINE_STEPS.find((entry) => entry.id === stepId);
	const help = getFeatureHelp(featureId);

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("pipeline.layouts.videoEyebrow")}
				title={
					step
						? getVideoPipelineStepLabel(t, step.id)
						: t("pipeline.layouts.stepFallback")
				}
				description={
					step ? getVideoPipelineStepDescription(t, step.id) : help.short
				}
				whatCanIDo={help.short}
				secondaryActions={<ExplainThisButton featureId={featureId} />}
			/>
			<ArtifactJourney
				videoId={videoId || null}
				title={t("pipeline.layouts.journeyPipeline")}
			/>
			{videoId ? <PipelineProgressPanel sourceId={videoId} /> : null}
			<div className={styles.content}>{children}</div>
		</div>
	);
}
