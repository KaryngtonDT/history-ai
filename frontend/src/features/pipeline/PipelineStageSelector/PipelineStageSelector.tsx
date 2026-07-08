import type { AIProvider } from "@/services/ai/types";
import {
	PIPELINE_STAGE_LABELS,
	type PipelineStageType,
} from "@/services/pipeline/types";
import type { RuntimeCapabilitySelectionView } from "@/services/runtime/types";
import styles from "./PipelineStageSelector.module.css";

interface PipelineStageSelectorProps {
	stage: PipelineStageType;
	providers: AIProvider[];
	selectedProviderId: string;
	selectionView?: RuntimeCapabilitySelectionView;
	onChange: (providerId: string) => void;
}

export function PipelineStageSelector({
	stage,
	providers,
	selectedProviderId,
	selectionView,
	onChange,
}: PipelineStageSelectorProps) {
	const enabledProviders = providers.filter((provider) => provider.enabled);

	return (
		<div className={styles.root}>
			<label className={styles.labelRow} htmlFor={`pipeline-stage-${stage}`}>
				<span className={styles.label}>{PIPELINE_STAGE_LABELS[stage]}</span>
				<select
					id={`pipeline-stage-${stage}`}
					className={styles.select}
					value={selectedProviderId}
					onChange={(event) => onChange(event.target.value)}
				>
					{enabledProviders.map((provider) => (
						<option key={provider.providerId} value={provider.providerId}>
							{provider.displayName}
						</option>
					))}
				</select>
			</label>
			{selectionView ? (
				<ul className={styles.meta}>
					{selectionView.recommendedDisplayName ? (
						<li>Recommended: {selectionView.recommendedDisplayName}</li>
					) : null}
					{selectionView.currentDisplayName ? (
						<li>Current: {selectionView.currentDisplayName}</li>
					) : null}
					{(selectionView.installedEngineIds?.length ?? 0) > 0 ? (
						<li>Installed: {selectionView.installedEngineIds?.join(", ")}</li>
					) : null}
					{selectionView.blockedReason ? (
						<li className={styles.blocked}>
							Blocked: {selectionView.blockedReason}
						</li>
					) : null}
				</ul>
			) : null}
		</div>
	);
}
