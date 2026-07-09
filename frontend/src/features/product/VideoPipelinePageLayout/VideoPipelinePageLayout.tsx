import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
import { ExplainThisButton } from "@/features/help";
import type { FeatureHelpId } from "@/features/help/content/features";
import { getFeatureHelp } from "@/features/help/content/features";
import {
	PipelineProgressPanel,
	PipelineSourceProvider,
} from "@/features/pipeline";
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

function VideoPipelinePageLayoutInner({
	stepId,
	featureId,
	children,
	videoId,
}: VideoPipelinePageLayoutProps & { videoId: string }) {
	const { t } = useTranslation();
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
			<PipelineProgressPanel sourceId={videoId} />
			<div className={styles.content}>{children}</div>
		</div>
	);
}

export function VideoPipelinePageLayout({
	stepId,
	featureId,
	children,
}: VideoPipelinePageLayoutProps) {
	const { videoId = "" } = useParams();

	if (!videoId) {
		return (
			<VideoPipelinePageLayoutInner
				stepId={stepId}
				featureId={featureId}
				videoId=""
			>
				{children}
			</VideoPipelinePageLayoutInner>
		);
	}

	return (
		<PipelineSourceProvider sourceId={videoId}>
			<VideoPipelinePageLayoutInner
				stepId={stepId}
				featureId={featureId}
				videoId={videoId}
			>
				{children}
			</VideoPipelinePageLayoutInner>
		</PipelineSourceProvider>
	);
}
