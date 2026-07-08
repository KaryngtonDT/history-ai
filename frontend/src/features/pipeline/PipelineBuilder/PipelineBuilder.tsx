import { useCallback, useEffect, useMemo, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import type { AIProvider } from "@/services/ai/types";
import { pipelineService } from "@/services/pipeline/PipelineService";
import {
	PIPELINE_STAGE_ORDER,
	type PipelineStage,
	type PipelineStageType,
} from "@/services/pipeline/types";
import { runtimeService } from "@/services/runtime/RuntimeService";
import type { RuntimeCapabilitySelectionView } from "@/services/runtime/types";
import { PipelineStageSelector } from "../PipelineStageSelector";
import styles from "./PipelineBuilder.module.css";

function providersFromSelectionView(
	view: RuntimeCapabilitySelectionView,
	stage: PipelineStageType,
): AIProvider[] {
	const capability = stage;
	const installed = view.installedEngineIds ?? [];
	const adapterKey = view.adapterKey ?? view.currentEngineId ?? "";

	if (installed.length === 0 && adapterKey) {
		return [
			{
				providerId: adapterKey,
				displayName: view.currentDisplayName ?? adapterKey,
				capability,
				enabled: view.executable ?? true,
			},
		];
	}

	return installed.map((engineId) => ({
		providerId: mapEngineToAdapter(engineId),
		displayName: engineId,
		capability,
		enabled: true,
	}));
}

function mapEngineToAdapter(engineId: string): string {
	const map: Record<string, string> = {
		faster_whisper_large_v3: "faster_whisper",
		ollama_gemma3: "ollama",
		ollama_qwen3: "ollama",
		ollama_deepseek_r1_distill: "ollama",
		openvoice_v2: "openvoice",
		ffmpeg_nvenc: "ffmpeg",
		ffmpeg_av1: "ffmpeg",
	};
	return map[engineId] ?? engineId;
}

export function PipelineBuilder() {
	const [selectionViews, setSelectionViews] = useState<
		Map<PipelineStageType, RuntimeCapabilitySelectionView>
	>(new Map());
	const [stages, setStages] = useState<PipelineStage[]>([]);
	const [saveAsDefault, setSaveAsDefault] = useState(true);
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [success, setSuccess] = useState<string | null>(null);

	const providersByStage = useMemo(() => {
		const map = new Map<PipelineStageType, AIProvider[]>();

		for (const stageType of PIPELINE_STAGE_ORDER) {
			const view = selectionViews.get(stageType);
			if (view) {
				map.set(stageType, providersFromSelectionView(view, stageType));
			}
		}

		return map;
	}, [selectionViews]);

	const loadData = useCallback(async () => {
		setLoading(true);
		setError(null);

		const [configuration, ...views] = await Promise.all([
			pipelineService.loadConfiguration(),
			...PIPELINE_STAGE_ORDER.map((stage) =>
				runtimeService.getCapabilitySelectionView(stage),
			),
		]);

		const viewMap = new Map<
			PipelineStageType,
			RuntimeCapabilitySelectionView
		>();
		PIPELINE_STAGE_ORDER.forEach((stage, index) => {
			viewMap.set(stage, views[index]);
		});

		setSelectionViews(viewMap);
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
					Choose the AI engine used at each step. Options are resolved by the
					Runtime kernel (installed, compatible, recommended).
				</p>
			</header>

			<Card className={styles.card}>
				{PIPELINE_STAGE_ORDER.map((stageType) => {
					const providers = providersByStage.get(stageType) ?? [];
					const selectionView = selectionViews.get(stageType);
					const selected =
						stages.find((stage) => stage.stage === stageType)?.providerId ??
						selectionView?.adapterKey ??
						providers.find((provider) => provider.enabled)?.providerId ??
						"";

					return (
						<PipelineStageSelector
							key={stageType}
							stage={stageType}
							providers={providers}
							selectedProviderId={selected}
							selectionView={selectionView}
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
