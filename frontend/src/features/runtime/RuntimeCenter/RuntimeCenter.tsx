import { useCallback, useEffect, useState } from "react";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { runtimeService } from "@/services/runtime/RuntimeService";
import type {
	RuntimeCapabilityMaturityOverview,
	RuntimeCompatibilitySummary,
	RuntimeEngine,
	RuntimeEngineCompatibility,
	RuntimeEngineTestResult,
	RuntimeHardwareOverview,
	RuntimeOverview,
	RuntimeReadiness,
	RuntimeRecommendation,
	RuntimeValidationReport,
} from "@/services/runtime/types";
import styles from "./RuntimeCenter.module.css";

function statusVariant(status: string): "success" | "warning" | "danger" | "neutral" {
	if (status === "ready" || status === "pass") return "success";
	if (status === "mock" || status === "degraded") return "warning";
	if (
		status === "unavailable" ||
		status === "fail" ||
		status === "missing" ||
		status === "misconfigured" ||
		status === "blocked"
	) {
		return "danger";
	}
	return "neutral";
}

function modeLabel(mode: string): string {
	if (mode === "real") return "REAL";
	if (mode === "shim") return "SHIM";
	if (mode === "mock") return "MOCK";
	return mode.toUpperCase();
}

function formatGb(value?: number | null): string {
	if (value == null) return "unknown";
	return `${value} GB`;
}

function EngineBlockedExplanation({
	engine,
	compatibility,
}: {
	engine: RuntimeEngine;
	compatibility: RuntimeEngineCompatibility;
}) {
	const [expanded, setExpanded] = useState(false);
	const isBlocked =
		compatibility.status === "blocked" ||
		compatibility.status === "missing" ||
		compatibility.status === "misconfigured";

	if (!isBlocked) {
		return null;
	}

	return (
		<div className={styles.blockedBox}>
			<Button
				type="button"
				variant="secondary"
				onClick={() => setExpanded((current) => !current)}
			>
				{expanded ? "Hide explanation" : "Why blocked?"}
			</Button>
			{expanded && (
				<div className={styles.blockedDetails}>
					<p>
						<strong>Why?</strong> {compatibility.humanReason}
					</p>
					{compatibility.missingRequirements.length > 0 && (
						<div>
							<strong>Missing:</strong>
							<ul className={styles.requirementList}>
								{compatibility.missingRequirements.map((item) => (
									<li key={item}>{item}</li>
								))}
							</ul>
						</div>
					)}
					{compatibility.recommendedAlternative && (
						<p>
							<strong>Recommended for this machine:</strong>{" "}
							{compatibility.recommendedAlternative}
						</p>
					)}
					{(compatibility.fixTypeLabels?.length ?? 0) > 0 && (
						<div>
							<strong>Possible fixes:</strong>
							<ul className={styles.requirementList}>
								{compatibility.fixTypeLabels?.map((fix) => (
									<li key={fix}>{fix}</li>
								))}
							</ul>
						</div>
					)}
					{compatibility.documentationLink && (
						<p className={styles.meta}>
							Docs: {compatibility.documentationLink}
						</p>
					)}
					<p className={styles.meta}>
						Provider: {compatibility.providerLabel ?? compatibility.provider}
						{engine.errorReason ? ` · ${engine.errorReason}` : ""}
					</p>
				</div>
			)}
		</div>
	);
}

export function RuntimeCenter() {
	const [overview, setOverview] = useState<RuntimeOverview | null>(null);
	const [readiness, setReadiness] = useState<RuntimeReadiness | null>(null);
	const [hardware, setHardware] = useState<RuntimeHardwareOverview | null>(null);
	const [compatibility, setCompatibility] =
		useState<RuntimeCompatibilitySummary | null>(null);
	const [maturity, setMaturity] = useState<RuntimeCapabilityMaturityOverview | null>(
		null,
	);
	const [recommendations, setRecommendations] = useState<RuntimeRecommendation[]>(
		[],
	);
	const [testResults, setTestResults] = useState<
		Record<string, RuntimeEngineTestResult>
	>({});
	const [validation, setValidation] = useState<RuntimeValidationReport | null>(
		null,
	);
	const [loading, setLoading] = useState(true);
	const [busy, setBusy] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setLoading(true);
		setError(null);
		try {
			const [
				nextOverview,
				nextReadiness,
				nextRecommendations,
				nextHardware,
				nextCompatibility,
				nextMaturity,
			] = await Promise.all([
				runtimeService.getOverview(),
				runtimeService.getReadiness(),
				runtimeService.getRecommendations(),
				runtimeService.getHardware(),
				runtimeService.getCompatibility(),
				runtimeService.getCapabilityMaturity(),
			]);
			setOverview(nextOverview);
			setReadiness(nextReadiness);
			setRecommendations(nextRecommendations);
			setHardware(nextHardware);
			setCompatibility(nextCompatibility);
			setMaturity(nextMaturity);
		} catch {
			setError("Unable to load runtime platform.");
		} finally {
			setLoading(false);
		}
	}, []);

	useEffect(() => {
		void load();
	}, [load]);

	const runValidation = async () => {
		setBusy("validate");
		try {
			setValidation(await runtimeService.validatePipeline());
			await load();
		} finally {
			setBusy(null);
		}
	};

	const runBenchmark = async () => {
		setBusy("benchmark");
		try {
			await runtimeService.runFullBenchmark();
			await load();
		} finally {
			setBusy(null);
		}
	};

	const runProvisionCompatible = async () => {
		setBusy("provision");
		try {
			await runtimeService.provisionCompatibleAll();
			await load();
		} finally {
			setBusy(null);
		}
	};

	const provisionEngine = async (engine: RuntimeEngine) => {
		setBusy(`provision-${engine.id}`);
		try {
			await runtimeService.provisionEngine(engine.id);
			await load();
		} finally {
			setBusy(null);
		}
	};

	const testEngine = async (engine: RuntimeEngine) => {
		setBusy(engine.id);
		try {
			const result = await runtimeService.testEngine(engine.id);
			setTestResults((current) => ({ ...current, [engine.id]: result }));
			await load();
		} finally {
			setBusy(null);
		}
	};

	if (loading) {
		return <Spinner label="Loading AI runtime" />;
	}

	if (error || !overview || !readiness || !hardware || !compatibility) {
		return <p className={styles.error}>{error ?? "Runtime unavailable."}</p>;
	}

	const caps = hardware.capabilities;

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>AI Runtime Platform</h2>
					<p className={styles.subtitle}>{overview.principle}</p>
				</div>
				<Badge variant={statusVariant(overview.status)}>
					{overview.status.toUpperCase()}
				</Badge>
			</header>

			<div className={styles.actions}>
				<Button
					type="button"
					onClick={() => void runValidation()}
					disabled={busy !== null}
				>
					{busy === "validate" ? "Validating…" : "Verify Pipeline"}
				</Button>
				<Button
					type="button"
					variant="secondary"
					onClick={() => void runProvisionCompatible()}
					disabled={busy !== null}
				>
					{busy === "provision"
						? "Provisioning…"
						: "Provision All Compatible Engines"}
				</Button>
				<Button
					type="button"
					variant="secondary"
					onClick={() => void runBenchmark()}
					disabled={busy !== null}
				>
					{busy === "benchmark" ? "Benchmarking…" : "Benchmark All"}
				</Button>
			</div>

			<section className={styles.section}>
				<h3>Hardware Profile</h3>
				<Card className={styles.card}>
					<div className={styles.engineHeader}>
						<strong>{hardware.profile.label}</strong>
						<Badge variant="neutral">{hardware.profile.type.toUpperCase()}</Badge>
					</div>
					<p className={styles.meta}>{hardware.profile.summary}</p>
					<ul className={styles.machineList}>
						<li>CPU: {caps.cpuModel ?? "unknown"}</li>
						<li>RAM: {formatGb(caps.ramTotalGb)}</li>
						<li>RAM free: {formatGb(caps.ramAvailableGb)}</li>
						<li>GPU: {caps.gpuName ?? "none detected"}</li>
						<li>CUDA: {caps.cudaAvailable ? "yes" : "no"}</li>
						<li>ROCm: {caps.rocmAvailable ? "yes" : "no"}</li>
						<li>DirectML: {caps.directMlAvailable ? "yes" : "no"}</li>
						<li>Docker GPU access: {caps.dockerGpuAccess ? "yes" : "no"}</li>
						<li>Docker memory: {formatGb(caps.dockerMemoryLimitGb)}</li>
						<li>Disk free: {formatGb(caps.diskFreeGb)}</li>
					</ul>
					<p className={styles.meta}>
						<strong>Recommended pipeline:</strong> Speech Faster Whisper ·
						Translation Gemma 3 · TTS F5-TTS · Voice OpenVoice · Lip Sync{" "}
						{hardware.recommendedPipeline.lipSync} · Render{" "}
						{hardware.recommendedPipeline.render}
					</p>
					<div className={styles.summaryGrid}>
						<div>
							<strong>Ready now</strong>
							<p>{compatibility.readyNow.join(", ") || "none"}</p>
						</div>
						<div>
							<strong>Blocked by hardware</strong>
							<p>{compatibility.blockedByHardware.join(", ") || "none"}</p>
						</div>
						<div>
							<strong>Blocked by installation</strong>
							<p>{compatibility.blockedByInstall.join(", ") || "none"}</p>
						</div>
					</div>
				</Card>
			</section>

			<div className={styles.grid}>
				<Card className={styles.card}>
					<h3>Health</h3>
					<p>Score: {overview.health.score}%</p>
					<p>
						Engines ready: {overview.health.healthyEngines}/
						{overview.health.totalEngines}
					</p>
				</Card>
				<Card className={styles.card}>
					<h3>Readiness</h3>
					<p>
						{readiness.readyCount}/{readiness.totalCount} engines ready
					</p>
					{readiness.issues.length > 0 && (
						<ul className={styles.issueList}>
							{readiness.issues.map((issue) => (
								<li key={issue}>{issue}</li>
							))}
						</ul>
					)}
				</Card>
			</div>

			<section className={styles.section}>
				<h3>Engines</h3>
				<div className={styles.engineGrid}>
					{readiness.engines.map((engine) => {
						const lastTest = testResults[engine.id];
						const engineCompatibility = engine.compatibility;
						return (
							<Card key={engine.id} className={styles.engineCard}>
								<div className={styles.engineHeader}>
									<strong>{engine.displayName}</strong>
									<div className={styles.badges}>
										<Badge variant={statusVariant(engine.status)}>
											{engine.status.toUpperCase()}
										</Badge>
										<Badge variant={engine.mode === "real" ? "success" : "warning"}>
											{modeLabel(engine.mode)}
										</Badge>
										{engineCompatibility && (
											<Badge
												variant={
													engineCompatibility.hardwareCompatible === false
														? "danger"
														: "success"
												}
											>
												{engineCompatibility.hardwareCompatible === false
													? "HW BLOCKED"
													: "HW OK"}
											</Badge>
										)}
									</div>
								</div>
								<p className={styles.meta}>
									{engine.roleLabel ?? engine.capability}
									{engine.tierLabel ? ` · ${engine.tierLabel}` : ""}
									{engineCompatibility?.providerLabel
										? ` · ${engineCompatibility.providerLabel}`
										: ""}
									{engine.configured ? " · configured" : ""}
									{engine.executableFound ? " · executable" : " · no executable"}
									{engine.modelFound ? " · model ok" : " · model missing"}
								</p>
								{engineCompatibility && engineCompatibility.status !== "ready" && (
									<p className={styles.warning}>{engineCompatibility.humanReason}</p>
								)}
								{engine.errorReason && !engineCompatibility && (
									<p className={styles.warning}>{engine.errorReason}</p>
								)}
								{engineCompatibility && (
									<EngineBlockedExplanation
										engine={engine}
										compatibility={engineCompatibility}
									/>
								)}
								{engine.requirements && engine.requirements.length > 0 && (
									<ul className={styles.requirementList}>
										{engine.requirements.map((req) => (
											<li key={req.key}>
												{req.label}: {req.satisfied ? "ok" : "missing"}
											</li>
										))}
									</ul>
								)}
								{lastTest && (
									<p className={styles.meta}>
										Last test: {lastTest.ok ? "PASS" : "FAIL"}
										{lastTest.durationMs != null
											? ` (${lastTest.durationMs}ms)`
											: ""}
										{lastTest.outputSample ? ` — ${lastTest.outputSample}` : ""}
										{lastTest.error ? ` — ${lastTest.error}` : ""}
									</p>
								)}
								{engine.installCommand && (
									<p className={styles.meta}>Install: {engine.installCommand}</p>
								)}
								<div className={styles.engineActions}>
									{engine.autoProvisionSupported && (
										<Button
											type="button"
											variant="secondary"
											onClick={() => void provisionEngine(engine)}
											disabled={busy !== null}
										>
											{busy === `provision-${engine.id}` ? "Installing…" : "Install"}
										</Button>
									)}
									<Button
										type="button"
										variant="secondary"
										onClick={() => void testEngine(engine)}
										disabled={busy !== null}
									>
										{busy === engine.id ? "Testing…" : "Test"}
									</Button>
								</div>
							</Card>
						);
					})}
				</div>
			</section>

			{maturity && (
				<section className={styles.section}>
					<h3>Capability Maturity ({maturity.totalEngines} engines)</h3>
					<div className={styles.engineGrid}>
						{maturity.capabilities.map((cap) => (
							<Card key={cap.capability} className={styles.engineCard}>
								<div className={styles.engineHeader}>
									<strong>{cap.label}</strong>
									<Badge variant="neutral">{cap.maturityLabel}</Badge>
								</div>
								<p className={styles.meta}>
									Default: {cap.defaultDisplayName ?? "—"}
									{cap.videoPipeline ? " · video pipeline" : " · platform capability"}
								</p>
								<ul className={styles.requirementList}>
									{cap.engines.map((eng) => (
										<li key={eng.id}>
											{eng.displayName} ({eng.tierLabel})
										</li>
									))}
								</ul>
							</Card>
						))}
					</div>
				</section>
			)}

			<section className={styles.section}>
				<h3>Recommendations</h3>
				<div className={styles.engineGrid}>
					{recommendations.map((item) => (
						<Card key={item.capability} className={styles.engineCard}>
							<strong>{item.label}</strong>
							<p>{item.recommendedDisplayName ?? "No recommendation"}</p>
							<p className={styles.meta}>{item.reason}</p>
						</Card>
					))}
				</div>
			</section>

			{validation && (
				<section className={styles.section}>
					<h3>Last Validation</h3>
					<Badge variant={statusVariant(validation.status)}>
						{validation.status.toUpperCase()}
					</Badge>
					<ul className={styles.stepList}>
						{validation.steps.map((step) => (
							<li key={step.capability}>
								{step.capability}: {step.executedEngineId} ({step.status})
							</li>
						))}
					</ul>
				</section>
			)}
		</div>
	);
}
