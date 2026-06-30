import { useCallback, useEffect, useMemo, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { aiEngineService } from "@/services/ai/AIEngineService";
import type { AIEngine } from "@/services/ai/types";
import { pipelineService } from "@/services/pipeline/PipelineService";
import {
	PIPELINE_STAGE_ORDER,
	type PipelineStage,
	type PipelineStageType,
} from "@/services/pipeline/types";
import { PipelineStageSelector } from "../PipelineStageSelector";
import styles from "./PipelineBuilder.module.css";

export function PipelineBuilder() {
	const [engines, setEngines] = useState<AIEngine[]>([]);
	const [stages, setStages] = useState<PipelineStage[]>([]);
	const [saveAsDefault, setSaveAsDefault] = useState(true);
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [success, setSuccess] = useState<string | null>(null);

	const providersByStage = useMemo(() => {
		const map = new Map<PipelineStageType, AIEngine["providers"]>();

		for (const engine of engines) {
			map.set(engine.capability as PipelineStageType, engine.providers);
		}

		return map;
	}, [engines]);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const [loadedEngines, configuration] = await Promise.all([
			aiEngineService.listEngines(),
			pipelineService.loadConfiguration(),
		]);

		setEngines(loadedEngines);
		setStages(configuration.stages);
		setLoading(false);
	}, []);

	useEffect(() => {
		void loadData();
	}, [loadData]);

	const updateStage = (stageType: PipelineStageType, providerId: string) => {
		setStages((current) =>
			current.map((stage) =>
				stage.stage === stageType ? { ...stage, providerId } : stage,
			),
		);
	};

	const handleSave = async () => {
		setSaving(true);
		setError(null);
		setSuccess(null);

		try {
			if (saveAsDefault) {
				await pipelineService.saveConfiguration(stages);
			}

			setSuccess("Pipeline configuration saved.");
		} catch {
			setError("Unable to save pipeline configuration.");
		} finally {
			setSaving(false);
		}
	};

	const handleReset = async () => {
		setSaving(true);
		setError(null);
		setSuccess(null);

		try {
			const configuration = await pipelineService.resetConfiguration();
			setStages(configuration.stages);
			setSuccess("Pipeline reset to defaults.");
		} catch {
			setError("Unable to reset pipeline configuration.");
		} finally {
			setSaving(false);
		}
	};

	if (loading) {
		return <Spinner label="Loading pipeline configuration" />;
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<h2 className={styles.title}>Processing Pipeline</h2>
				<p className={styles.description}>
					Choose the AI engine used at each step before starting video
					processing.
				</p>
			</header>

			<Card className={styles.card}>
				{PIPELINE_STAGE_ORDER.map((stageType) => {
					const providers = providersByStage.get(stageType) ?? [];
					const selected =
						stages.find((stage) => stage.stage === stageType)?.providerId ??
						providers.find((provider) => provider.enabled)?.providerId ??
						"";

					return (
						<PipelineStageSelector
							key={stageType}
							stage={stageType}
							providers={providers}
							selectedProviderId={selected}
							onChange={(providerId) => updateStage(stageType, providerId)}
						/>
					);
				})}

				<label className={styles.checkbox}>
					<input
						type="checkbox"
						checked={saveAsDefault}
						onChange={(event) => setSaveAsDefault(event.target.checked)}
					/>
					Save as default configuration
				</label>

				<div className={styles.actions}>
					<Button
						type="button"
						onClick={() => void handleSave()}
						disabled={saving}
					>
						{saving ? "Saving..." : "Save Configuration"}
					</Button>
					<Button
						type="button"
						variant="secondary"
						onClick={() => void handleReset()}
						disabled={saving}
					>
						Reset Defaults
					</Button>
				</div>

				{error ? <p className={styles.error}>{error}</p> : null}
				{success ? <p className={styles.success}>{success}</p> : null}
			</Card>
		</div>
	);
}
