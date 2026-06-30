import type { AIProvider } from "@/services/ai/types";
import {
	PIPELINE_STAGE_LABELS,
	type PipelineStageType,
} from "@/services/pipeline/types";
import styles from "./PipelineStageSelector.module.css";

interface PipelineStageSelectorProps {
	stage: PipelineStageType;
	providers: AIProvider[];
	selectedProviderId: string;
	onChange: (providerId: string) => void;
}

export function PipelineStageSelector({
	stage,
	providers,
	selectedProviderId,
	onChange,
}: PipelineStageSelectorProps) {
	const enabledProviders = providers.filter((provider) => provider.enabled);

	return (
		<label className={styles.root} htmlFor={`pipeline-stage-${stage}`}>
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
	);
}
