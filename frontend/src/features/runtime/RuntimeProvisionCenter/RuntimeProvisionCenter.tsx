import { useCallback, useEffect, useState } from "react";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import type {
	CapabilitySelectionMode,
	RuntimeEngineManagement,
	RuntimeManagedCapability,
	RuntimeManagedEngine,
} from "@/services/runtime/managementTypes";
import { runtimeService } from "@/services/runtime/RuntimeService";
import styles from "./RuntimeProvisionCenter.module.css";

function statusVariant(
	engine: RuntimeManagedEngine,
): "success" | "warning" | "danger" | "neutral" {
	if (engine.ready) return "success";
	if (engine.mock) return "warning";
	if (engine.blocked || engine.misconfigured) return "danger";
	return "neutral";
}

function EngineCard({
	engine,
	onAction,
	busy,
}: {
	engine: RuntimeManagedEngine;
	onAction: (action: string, engineId: string) => void;
	busy: string | null;
}) {
	return (
		<Card className={styles.engineCard}>
			<div className={styles.engineHeader}>
				<div>
					<h4>{engine.displayName}</h4>
					<p className={styles.meta}>{engine.engineId}</p>
				</div>
				<div className={styles.badges}>
					{engine.isCurrent && <Badge variant="success">Current</Badge>}
					{engine.isRecommended && <Badge variant="neutral">Recommended</Badge>}
					{engine.isReference && <Badge variant="neutral">Reference</Badge>}
					<Badge variant={statusVariant(engine)}>{engine.status}</Badge>
				</div>
			</div>
			<dl className={styles.stats}>
				<div>
					<dt>Provider</dt>
					<dd>{engine.provider}</dd>
				</div>
				<div>
					<dt>Benchmark</dt>
					<dd>{engine.benchmarkScore ?? "—"}</dd>
				</div>
				<div>
					<dt>Avg duration</dt>
					<dd>
						{engine.averageDurationSeconds != null
							? `${engine.averageDurationSeconds}s`
							: "—"}
					</dd>
				</div>
				<div>
					<dt>Success</dt>
					<dd>
						{engine.averageAccuracy != null
							? `${engine.averageAccuracy}%`
							: "—"}
					</dd>
				</div>
			</dl>
			{engine.blockedReason && (
				<p className={styles.blocked}>{engine.blockedReason}</p>
			)}
			<div className={styles.actions}>
				<Button
					type="button"
					disabled={busy !== null || !engine.autoProvisionSupported}
					onClick={() => onAction("install", engine.engineId)}
				>
					Install
				</Button>
				<Button
					type="button"
					variant="secondary"
					disabled={busy !== null}
					onClick={() => onAction("validate", engine.engineId)}
				>
					Validate
				</Button>
				<Button
					type="button"
					variant="secondary"
					disabled={busy !== null}
					onClick={() => onAction("benchmark", engine.engineId)}
				>
					Benchmark
				</Button>
				<Button
					type="button"
					variant="secondary"
					disabled={busy !== null}
					onClick={() => onAction("repair", engine.engineId)}
				>
					Repair
				</Button>
				<Button
					type="button"
					variant="secondary"
					disabled={busy !== null}
					onClick={() => onAction("remove", engine.engineId)}
				>
					Remove
				</Button>
			</div>
		</Card>
	);
}

function CapabilitySection({
	capability,
	onModeChange,
	onSelectEngine,
	onAction,
	busy,
}: {
	capability: RuntimeManagedCapability;
	onModeChange: (capability: string, mode: CapabilitySelectionMode) => void;
	onSelectEngine: (capability: string, engineId: string) => void;
	onAction: (action: string, engineId: string) => void;
	busy: string | null;
}) {
	return (
		<section className={styles.capabilitySection}>
			<div className={styles.capabilityHeader}>
				<div>
					<h3>{capability.label}</h3>
					<p className={styles.meta}>
						Current: {capability.currentEngineId ?? "—"} · Recommended:{" "}
						{capability.recommendedEngineId ?? "—"}
					</p>
				</div>
				<div className={styles.modeSwitch}>
					{(["auto", "manual", "locked"] as CapabilitySelectionMode[]).map(
						(mode) => (
							<Button
								key={mode}
								type="button"
								variant={
									capability.selectionMode === mode ? "primary" : "secondary"
								}
								onClick={() => onModeChange(capability.capability, mode)}
							>
								{mode}
							</Button>
						),
					)}
				</div>
			</div>
			{capability.selectionMode !== "auto" && (
				<div className={styles.manualPick}>
					{capability.engines.map((engine) => (
						<label key={engine.engineId} className={styles.radioRow}>
							<input
								type="radio"
								name={`selection-${capability.capability}`}
								checked={
									capability.selectionMode === "locked"
										? capability.currentEngineId === engine.engineId
										: capability.currentEngineId === engine.engineId
								}
								onChange={() =>
									onSelectEngine(capability.capability, engine.engineId)
								}
							/>
							<span>{engine.displayName}</span>
							{engine.blocked && <Badge variant="danger">Blocked</Badge>}
						</label>
					))}
				</div>
			)}
			<div className={styles.engineGrid}>
				{capability.engines.map((engine) => (
					<EngineCard
						key={engine.engineId}
						engine={engine}
						onAction={onAction}
						busy={busy}
					/>
				))}
			</div>
		</section>
	);
}

export function RuntimeProvisionCenter() {
	const [management, setManagement] = useState<RuntimeEngineManagement | null>(
		null,
	);
	const [error, setError] = useState<string | null>(null);
	const [busy, setBusy] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		try {
			setManagement(await runtimeService.getEngineManagement());
		} catch (loadError) {
			setError(
				loadError instanceof Error
					? loadError.message
					: "Failed to load engine management data.",
			);
		}
	}, []);

	useEffect(() => {
		void load();
	}, [load]);

	const handleModeChange = async (
		capability: string,
		mode: CapabilitySelectionMode,
	) => {
		setBusy(`mode-${capability}`);
		try {
			await runtimeService.updateSelection({
				capabilityModes: { [capability]: mode },
			});
			await load();
		} finally {
			setBusy(null);
		}
	};

	const handleSelectEngine = async (capability: string, engineId: string) => {
		if (!management) return;
		const cap = management.capabilities.find(
			(item) => item.capability === capability,
		);
		if (!cap) return;

		setBusy(`select-${capability}`);
		try {
			if (cap.selectionMode === "locked") {
				await runtimeService.updateSelection({
					lockedSelections: { [capability]: engineId },
				});
			} else {
				await runtimeService.updateSelection({
					manualSelections: { [capability]: engineId },
				});
			}
			await load();
		} finally {
			setBusy(null);
		}
	};

	const handleAction = async (action: string, engineId: string) => {
		setBusy(`${action}-${engineId}`);
		try {
			if (action === "install") await runtimeService.installEngine(engineId);
			if (action === "validate") await runtimeService.validateEngine(engineId);
			if (action === "benchmark") await runtimeService.testEngine(engineId);
			if (action === "repair") await runtimeService.repairEngine(engineId);
			if (action === "remove") await runtimeService.removeEngine(engineId);
			await load();
		} finally {
			setBusy(null);
		}
	};

	if (!management && !error) {
		return (
			<div className={styles.loading}>
				<Spinner />
			</div>
		);
	}

	if (error) {
		return <p className={styles.error}>{error}</p>;
	}

	if (!management) {
		return null;
	}

	return (
		<div className={styles.root}>
			<p className={styles.principle}>{management.principle}</p>
			{management.capabilities.map((capability) => (
				<CapabilitySection
					key={capability.capability}
					capability={capability}
					onModeChange={handleModeChange}
					onSelectEngine={handleSelectEngine}
					onAction={handleAction}
					busy={busy}
				/>
			))}
		</div>
	);
}
