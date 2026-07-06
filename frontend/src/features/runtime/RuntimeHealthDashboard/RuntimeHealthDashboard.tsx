import { useCallback, useEffect, useState } from "react";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { runtimeService } from "@/services/runtime/RuntimeService";
import type { RuntimeDashboard } from "@/services/runtime/types";
import styles from "./RuntimeHealthDashboard.module.css";

function capabilityVariant(
	status: string,
): "success" | "warning" | "danger" | "neutral" {
	if (status === "ready") return "success";
	if (status === "partial") return "warning";
	if (status === "not_installed") return "neutral";
	if (status === "blocked" || status === "unknown") return "danger";
	return "neutral";
}

function capabilityIcon(status: string): string {
	if (status === "ready") return "🟢";
	if (status === "partial") return "🟡";
	if (status === "not_installed") return "⚪";
	return "🔴";
}

function boolIcon(value: boolean): string {
	return value ? "✅" : "❌";
}

function formatTimelineDate(at: string): string {
	if (!at) return "—";
	try {
		const date = new Date(at);
		const now = new Date();
		const sameDay =
			date.getFullYear() === now.getFullYear() &&
			date.getMonth() === now.getMonth() &&
			date.getDate() === now.getDate();
		if (sameDay) return "Today";
		const yesterday = new Date(now);
		yesterday.setDate(now.getDate() - 1);
		const isYesterday =
			date.getFullYear() === yesterday.getFullYear() &&
			date.getMonth() === yesterday.getMonth() &&
			date.getDate() === yesterday.getDate();
		if (isYesterday) return "Yesterday";
		return date.toLocaleDateString();
	} catch {
		return at;
	}
}

function UtilizationBar({
	label,
	value,
	display,
}: {
	label: string;
	value: number;
	display?: string;
}) {
	const clamped = Math.max(0, Math.min(100, value));
	return (
		<div className={styles.barRow}>
			<div className={styles.barLabel}>
				<span>{label}</span>
				<span>{display ?? `${clamped}%`}</span>
			</div>
			<div className={styles.barTrack}>
				<div className={styles.barFill} style={{ width: `${clamped}%` }} />
			</div>
		</div>
	);
}

export function RuntimeHealthDashboard() {
	const [dashboard, setDashboard] = useState<RuntimeDashboard | null>(null);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setLoading(true);
		setError(null);
		try {
			setDashboard(await runtimeService.getDashboard());
		} catch (loadError) {
			setError(
				loadError instanceof Error
					? loadError.message
					: "Failed to load runtime dashboard",
			);
		} finally {
			setLoading(false);
		}
	}, []);

	useEffect(() => {
		void load();
	}, [load]);

	if (loading) {
		return <Spinner label="Loading runtime health dashboard…" />;
	}

	if (error || !dashboard) {
		return (
			<p className={styles.error}>
				{error ?? "Runtime dashboard unavailable."}
			</p>
		);
	}

	const { summary, overallRuntimeScore, platformScore } = dashboard;

	return (
		<div className={styles.root}>
			<div className={styles.hero}>
				<Card className={styles.scoreCard}>
					<h2 className={styles.sectionTitle}>Overall Runtime Health</h2>
					<div className={styles.scoreValue}>
						{Math.round(summary.overallHealth)} / 100
					</div>
					<div className={styles.scoreGrade}>{overallRuntimeScore.grade}</div>
					<p className={styles.meta}>{overallRuntimeScore.summary}</p>
				</Card>

				<Card className={styles.scoreCard}>
					<h2 className={styles.sectionTitle}>Platform Health</h2>
					<div className={styles.scoreValue}>
						{Math.round(platformScore.score)}%
					</div>
					<div className={styles.scoreGrade}>{platformScore.grade}</div>
					<ul className={styles.breakdownList}>
						{platformScore.components.map((component) => (
							<li key={component.key} className={styles.breakdownItem}>
								<span>{component.label}</span>
								<span>
									{component.score == null ? "—" : `${component.score}%`}
								</span>
							</li>
						))}
					</ul>
				</Card>
			</div>

			<Card>
				<h2 className={styles.sectionTitle}>Runtime Summary</h2>
				<div className={styles.summaryGrid}>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Hardware Profile</span>
						<span className={styles.summaryValue}>
							{summary.hardwareProfile.toUpperCase()}
						</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Runtime Status</span>
						<span className={styles.summaryValue}>{summary.runtimeStatus}</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Provisioning</span>
						<span className={styles.summaryValue}>
							{Math.round(summary.provisioningPercent)}%
						</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Compatible Engines</span>
						<span className={styles.summaryValue}>
							{summary.compatibleEnginesReady} /{" "}
							{summary.compatibleEnginesTotal}
						</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Premium Engines</span>
						<span className={styles.summaryValue}>
							{summary.premiumEnginesReady} / {summary.premiumEnginesTotal}
						</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Benchmarks Passed</span>
						<span className={styles.summaryValue}>
							{Math.round(summary.benchmarksPassedPercent)}%
						</span>
					</div>
					<div className={styles.summaryItem}>
						<span className={styles.summaryLabel}>Last Validation</span>
						<span className={styles.summaryValue}>
							{summary.lastValidation?.relative ?? "—"}
						</span>
					</div>
				</div>
			</Card>

			<section className={styles.section}>
				<h2 className={styles.sectionTitle}>Capabilities</h2>
				<div className={styles.grid}>
					{dashboard.capabilityStatuses.map((capability) => {
						const score = dashboard.capabilityScores.find(
							(item) => item.capability === capability.capability,
						);
						return (
							<Card key={capability.capability} className={styles.capabilityCard}>
								<div className={styles.capabilityHeader}>
									<strong>{capability.label}</strong>
									<Badge variant={capabilityVariant(capability.status)}>
										{capabilityIcon(capability.status)} {capability.statusLabel}
									</Badge>
								</div>
								{score && (
									<p className={styles.meta}>Capability score: {score.score}%</p>
								)}
								{score?.reason && (
									<p className={styles.meta}>Reason: {score.reason}</p>
								)}
								{capability.referenceDisplayName && (
									<p className={styles.meta}>
										Reference: {capability.referenceDisplayName}
									</p>
								)}
								{capability.recommendedEngineId && (
									<p className={styles.meta}>
										Recommended:{" "}
										{capability.recommendedDisplayName ?? capability.recommendedEngineId}
									</p>
								)}
								{capability.currentEngineId && (
									<p className={styles.meta}>
										Current:{" "}
										{capability.currentDisplayName ?? capability.currentEngineId}
									</p>
								)}
								{(capability.installedEngineIds?.length ?? 0) > 0 && (
									<p className={styles.meta}>
										Installed: {capability.installedEngineIds?.join(", ")}
									</p>
								)}
								{capability.blockedReason && (
									<p className={styles.meta}>Blocked: {capability.blockedReason}</p>
								)}
								{capability.benchmark && (
									<p className={styles.meta}>
										Benchmark: {capability.benchmark.status}
									</p>
								)}
								{capability.providerLabel && (
									<p className={styles.meta}>Provider: {capability.providerLabel}</p>
								)}
								{capability.improvement && (
									<p className={styles.meta}>Improvement: {capability.improvement}</p>
								)}
							</Card>
						);
					})}
				</div>
			</section>

			<section className={styles.section}>
				<h2 className={styles.sectionTitle}>Hardware</h2>
				<Card>
					<p className={styles.meta}>
						Profile:{" "}
						<strong>
							{String(dashboard.hardware.profile?.label ?? summary.hardwareProfileLabel)}
						</strong>
					</p>
					<UtilizationBar
						label="CPU"
						value={100}
						display={dashboard.hardware.cpuModel}
					/>
					<UtilizationBar
						label="GPU"
						value={dashboard.hardware.gpuName === "none" ? 0 : 60}
						display={dashboard.hardware.gpuName}
					/>
					<div className={styles.boolRow}>
						<span>CUDA {boolIcon(dashboard.hardware.cudaAvailable)}</span>
						<span>ROCm {boolIcon(dashboard.hardware.rocmAvailable)}</span>
						<span>
							DirectML {boolIcon(dashboard.hardware.directMlAvailable)}
						</span>
					</div>
					<UtilizationBar
						label="RAM"
						value={dashboard.hardware.ramUtilization}
						display={`${dashboard.hardware.ramAvailableGb} / ${dashboard.hardware.ramTotalGb} GB`}
					/>
					<UtilizationBar
						label="Disk free"
						value={dashboard.hardware.diskUtilization}
						display={`${dashboard.hardware.diskFreeGb} GB free`}
					/>
					<div className={styles.boolRow}>
						<span>
							Docker GPU {boolIcon(dashboard.hardware.dockerGpuAccess)}
						</span>
						<span>WSL {boolIcon(dashboard.hardware.wsl2)}</span>
						<span>
							FFmpeg {boolIcon(dashboard.hardware.ffmpegAvailable)}
						</span>
						<span>
							Ollama {boolIcon(dashboard.hardware.ollamaAvailable)}
						</span>
						{dashboard.hardware.pythonVersion && (
							<span>Python {dashboard.hardware.pythonVersion}</span>
						)}
					</div>
				</Card>
			</section>

			{dashboard.engineRecommendations.length > 0 && (
				<section className={styles.section}>
					<h2 className={styles.sectionTitle}>Engine Recommendations</h2>
					<Card>
						{dashboard.engineRecommendations.map((item) => (
							<div key={item.capability} className={styles.recommendationRow}>
								<div>
									<strong>{item.label}</strong>
								</div>
								<div>
									<div className={styles.meta}>Reference</div>
									<div>{item.referenceDisplayName ?? "—"}</div>
								</div>
								<div>
									<div className={styles.meta}>Recommended</div>
									<div>{item.recommendedDisplayName ?? "—"}</div>
								</div>
								<div>
									<div className={styles.meta}>Current</div>
									<div>{item.currentDisplayName ?? "—"}</div>
								</div>
								<div>
									<div className={styles.meta}>Reason</div>
									<div>{item.reason}</div>
								</div>
							</div>
						))}
					</Card>
				</section>
			)}

			{dashboard.premiumFeatures.length > 0 && (
				<section className={styles.section}>
					<h2 className={styles.sectionTitle}>Premium Features</h2>
					<div className={styles.grid}>
						{dashboard.premiumFeatures.map((feature) => (
							<Card key={feature.engineId} className={styles.capabilityCard}>
								<strong>{feature.displayName}</strong>
								<p className={styles.meta}>{feature.humanReason}</p>
								{feature.needs.length > 0 && (
									<p className={styles.meta}>
										Needs: {feature.needs.join(", ")}
									</p>
								)}
								{feature.recommendedAlternative && (
									<p className={styles.meta}>
										Alternative: {feature.recommendedAlternative}
									</p>
								)}
							</Card>
						))}
					</div>
				</section>
			)}

			{dashboard.timeline.length > 0 && (
				<section className={styles.section}>
					<h2 className={styles.sectionTitle}>Runtime Timeline</h2>
					<Card className={styles.timeline}>
						{dashboard.timeline.map((event, index) => (
							<div
								key={`${event.at}-${event.type}-${index}`}
								className={styles.timelineItem}
							>
								<span>{formatTimelineDate(event.at)}</span>
								<span>{event.label}</span>
								<span>{event.detail}</span>
							</div>
						))}
					</Card>
				</section>
			)}

			<section className={styles.section}>
				<h2 className={styles.sectionTitle}>Overall Runtime Score</h2>
				<Card>
					<div className={styles.scoreValue}>
						{overallRuntimeScore.score} / 100
					</div>
					<div className={styles.scoreGrade}>{overallRuntimeScore.grade}</div>
					<p className={styles.meta}>{overallRuntimeScore.summary}</p>
					<ul className={styles.breakdownList}>
						{overallRuntimeScore.breakdown.map((item) => (
							<li key={item.key} className={styles.breakdownItem}>
								<span>
									{item.label} ({Math.round(item.weight * 100)}%)
								</span>
								<span>{item.score}%</span>
							</li>
						))}
					</ul>
				</Card>
			</section>

			{dashboard.warnings.length > 0 && (
				<section className={styles.section}>
					<h2 className={styles.sectionTitle}>Runtime Warnings</h2>
					<div className={styles.grid}>
						{dashboard.warnings.map((warning) => (
							<Card key={warning.engineId} className={styles.capabilityCard}>
								<strong>{warning.engineId}</strong>
								<p className={styles.meta}>{warning.humanReason}</p>
								{warning.recommendedAlternative && (
									<p className={styles.meta}>
										Try: {warning.recommendedAlternative}
									</p>
								)}
							</Card>
						))}
					</div>
				</section>
			)}

			<section className={styles.section}>
				<h2 className={styles.sectionTitle}>Runtime Recommendations</h2>
				<Card>
					<p>{dashboard.recommendations.summary}</p>
					<ul className={styles.breakdownList}>
						{dashboard.recommendations.pipeline.map((line) => (
							<li key={line.stage} className={styles.breakdownItem}>
								<span>
									{line.installed ? "✓" : "○"} {line.stage}
								</span>
								<span>{line.engineId}</span>
							</li>
						))}
					</ul>
				</Card>
			</section>

			<section className={styles.section}>
				<h2 className={styles.sectionTitle}>Shadow</h2>
				<div className={styles.shadowBox}>
					<p className={styles.shadowSpeaker}>
						{dashboard.shadowCommentary.speaker}
					</p>
					<p className={styles.shadowMessage}>
						{dashboard.shadowCommentary.message}
					</p>
				</div>
			</section>
		</div>
	);
}
