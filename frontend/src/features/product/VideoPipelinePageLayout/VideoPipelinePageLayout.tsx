import type { ReactNode } from "react";
import { useParams } from "react-router";
import { ArtifactJourney } from "@/features/artifacts";
import { ExplainThisButton } from "@/features/help";
import type { FeatureHelpId } from "@/features/help/content/features";
import { getFeatureHelp } from "@/features/help/content/features";
import { PageIntroduction } from "../PageIntroduction";
import { VIDEO_PIPELINE_STEPS, type VideoPipelineStepId } from "../videoRoutes";
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
	const { videoId = "" } = useParams();
	const step = VIDEO_PIPELINE_STEPS.find((entry) => entry.id === stepId);
	const help = getFeatureHelp(featureId);

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Video pipeline"
				title={step?.label ?? "Pipeline step"}
				description={step?.shortDescription ?? help.short}
				whatCanIDo={help.short}
				secondaryActions={<ExplainThisButton featureId={featureId} />}
			/>
			<ArtifactJourney videoId={videoId || null} title="Pipeline progress" />
			<div className={styles.content}>{children}</div>
		</div>
	);
}
