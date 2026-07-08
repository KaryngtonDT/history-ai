export interface RuntimeRequirement {
	key: string;
	label: string;
	satisfied: boolean;
	detail?: string | null;
}

export interface RuntimeEngineTestResult {
	engineId: string;
	status: string;
	ok: boolean;
	mode: string;
	durationMs?: number;
	executableFound?: boolean;
	modelFound?: boolean;
	fallbackUsed?: boolean;
	outputSample?: string | null;
	error?: string | null;
	at?: string;
}

export interface RuntimeEngineCompatibility {
	engineId: string;
	status: string;
	hardwareProfile: string;
	hardwareProfileLabel?: string;
	blockedReasonCode: string;
	blockedReasonLabel?: string;
	humanReason: string;
	missingRequirements: string[];
	recommendedAlternative?: string | null;
	canBeFixedByInstall: boolean;
	canBeFixedByHardware: boolean;
	canBeFixedByRemoteProvider: boolean;
	severity: string;
	provider: string;
	providerLabel?: string;
	fixTypes?: string[];
	fixTypeLabels?: string[];
	documentationLink?: string | null;
	hardwareCompatible?: boolean;
}

export interface RuntimeEngine {
	id: string;
	displayName: string;
	capability: string;
	status: string;
	mode: string;
	role?: string | null;
	roleLabel?: string | null;
	tier?: string | null;
	tierLabel?: string | null;
	configured: boolean;
	discovered: boolean;
	executableFound: boolean;
	modelFound: boolean;
	errorReason?: string | null;
	expectedModel?: string | null;
	version?: string | null;
	binaryPath?: string | null;
	requirements?: RuntimeRequirement[];
	autoProvisionSupported?: boolean;
	runtimeReady?: boolean;
	installCommand?: string | null;
	modelDownloadHint?: string | null;
	documentationPath?: string | null;
	lastTestResult?: RuntimeEngineTestResult | null;
	compatibility?: RuntimeEngineCompatibility | null;
}

export interface RuntimeReadiness {
	status: string;
	readyCount: number;
	totalCount: number;
	issues: string[];
	engines: RuntimeEngine[];
}

export interface RuntimeHealth {
	status: string;
	score: number;
	healthyEngines: number;
	totalEngines: number;
	issues: string[];
	lastCheckedAt?: string | null;
}

export interface RuntimeOverview {
	principle: string;
	status: string;
	health: RuntimeHealth;
	configuration: Record<string, unknown>;
	environment: Record<string, unknown>;
}

export interface RuntimeCatalog {
	installed: RuntimeEngine[];
	available: RuntimeEngine[];
	compatible: RuntimeEngine[];
}

export interface RuntimeCapabilityMaturityEngine {
	id: string;
	displayName: string;
	role: string;
	roleLabel: string;
	tier: string;
	tierLabel: string;
	hardware: Record<string, unknown>;
	provider: string;
	providerLabel: string;
	benchmarkModel: string;
}

export interface RuntimeCapabilityMaturity {
	capability: string;
	label: string;
	maturity: string;
	maturityLabel: string;
	videoPipeline: boolean;
	defaultEngineId?: string | null;
	defaultDisplayName?: string | null;
	engineCount: number;
	engines: RuntimeCapabilityMaturityEngine[];
}

export interface RuntimeCapabilityMaturityOverview {
	principle: string;
	capabilities: RuntimeCapabilityMaturity[];
	totalEngines: number;
	at: string;
}

export interface RuntimeRecommendation {
	capability: string;
	label: string;
	recommendedEngineId?: string | null;
	recommendedDisplayName?: string | null;
	requestedEngineId?: string | null;
	selectionMode: string;
	profile: string;
	reason: string;
	confidence: number;
}

export interface RuntimeProfile {
	value: string;
	label: string;
}

export interface RuntimeValidationStep {
	capability: string;
	requestedEngineId: string;
	executedEngineId: string;
	status: string;
	mode?: string;
	executableFound?: boolean;
	modelFound?: boolean;
	fallbackUsed: boolean;
	reason?: string | null;
	confidence: number;
}

export interface RuntimeValidationReport {
	pipelineId: string;
	status: string;
	steps: RuntimeValidationStep[];
	validatedAt: string;
}

export interface RuntimeHardwareCapabilities {
	cpuModel?: string | null;
	ramTotalGb?: number | null;
	ramAvailableGb?: number | null;
	gpuVendor?: string | null;
	gpuName?: string | null;
	vramGb?: number | null;
	cudaAvailable: boolean;
	rocmAvailable: boolean;
	directMlAvailable: boolean;
	dockerGpuAccess: boolean;
	wsl2: boolean;
	dockerMemoryLimitGb?: number | null;
	os?: string | null;
	pythonVersion?: string | null;
	ffmpegAvailable: boolean;
	ollamaAvailable: boolean;
	diskFreeGb?: number | null;
}

export interface RuntimeHardwareProfile {
	type: string;
	label: string;
	summary: string;
	capabilities: RuntimeHardwareCapabilities;
}

export interface RuntimeHardwareOverview {
	profile: RuntimeHardwareProfile;
	capabilities: RuntimeHardwareCapabilities;
	detectedAt: string;
	recommendedPipeline: Record<string, string>;
}

export interface RuntimeCompatibilitySummary {
	hardwareProfile: RuntimeHardwareProfile;
	engines: RuntimeEngineCompatibility[];
	blockedByHardware: string[];
	blockedByInstall: string[];
	readyNow: string[];
}

export interface RuntimeScoreBreakdown {
	key: string;
	label: string;
	score: number;
	weight: number;
	weightedContribution: number;
	explanation: string;
	improvement?: string | null;
}

export interface RuntimeOverallScore {
	score: number;
	grade: string;
	summary: string;
	breakdown: RuntimeScoreBreakdown[];
}

export interface RuntimePlatformScoreComponent {
	key: string;
	label: string;
	score: number | null;
	status: string;
}

export interface RuntimePlatformScore {
	score: number;
	grade: string;
	components: RuntimePlatformScoreComponent[];
}

export interface RuntimeDashboardSummary {
	overallHealth: number;
	hardwareProfile: string;
	hardwareProfileLabel: string;
	runtimeStatus: string;
	provisioningPercent: number;
	compatibleEnginesReady: number;
	compatibleEnginesTotal: number;
	premiumEnginesReady: number;
	premiumEnginesTotal: number;
	benchmarksPassedPercent: number;
	lastValidation?: {
		at?: string | null;
		status?: string | null;
		relative?: string | null;
	} | null;
}

export interface RuntimeDashboardCapability {
	capability: string;
	label: string;
	status: string;
	statusLabel: string;
	videoPipeline: boolean;
	referenceEngineId?: string | null;
	referenceDisplayName?: string | null;
	recommendedEngineId?: string | null;
	recommendedDisplayName?: string | null;
	currentEngineId?: string | null;
	currentDisplayName?: string | null;
	installedEngineIds?: string[];
	readyCount: number;
	engineCount: number;
	score?: number;
	blockedReason?: string | null;
	hardwareCompatible?: boolean | null;
	provider?: string | null;
	providerLabel?: string | null;
	benchmark?: { ok: boolean; status: string; at?: string | null } | null;
	health?: string | null;
	improvement?: string | null;
	alternative?: string | null;
}

export interface RuntimeDashboardCapabilityScore {
	capability: string;
	label: string;
	score: number;
	reason?: string | null;
}

export interface RuntimeDashboardHardware {
	profile: Record<string, unknown>;
	cpuModel: string;
	gpuName: string;
	gpuVendor?: string | null;
	cudaAvailable: boolean;
	rocmAvailable: boolean;
	directMlAvailable: boolean;
	dockerGpuAccess: boolean;
	wsl2: boolean;
	ramTotalGb: number;
	ramAvailableGb: number;
	ramUtilization: number;
	diskFreeGb: number;
	diskUtilization: number;
	ffmpegAvailable: boolean;
	ollamaAvailable: boolean;
	pythonVersion?: string | null;
	recommendedPipeline: Record<string, string>;
}

export interface RuntimeDashboardEngineRecommendation {
	capability: string;
	label: string;
	referenceEngineId?: string | null;
	referenceDisplayName?: string | null;
	recommendedEngineId?: string | null;
	recommendedDisplayName?: string | null;
	currentEngineId?: string | null;
	currentDisplayName?: string | null;
	reason: string;
}

export interface RuntimeDashboardPremiumFeature {
	engineId: string;
	displayName: string;
	status: string;
	humanReason: string;
	needs: string[];
	recommendedAlternative?: string | null;
}

export interface RuntimeDashboardTimelineEvent {
	at: string;
	type: string;
	label: string;
	detail: string;
}

export interface RuntimeDashboardWarning {
	engineId: string;
	severity: string;
	humanReason: string;
	recommendedAlternative?: string | null;
}

export interface RuntimeDashboardPipelineRecommendation {
	stage: string;
	engineId: string;
	installed: boolean;
}

export interface RuntimeDashboardRecommendations {
	summary: string;
	pipeline: RuntimeDashboardPipelineRecommendation[];
}

export interface RuntimeDashboardShadowCommentary {
	speaker: string;
	message: string;
	paragraphs: string[];
}

export interface RuntimeDashboard {
	title: string;
	generatedAt: string;
	overallRuntimeScore: RuntimeOverallScore;
	platformScore: RuntimePlatformScore;
	summary: RuntimeDashboardSummary;
	capabilityStatuses: RuntimeDashboardCapability[];
	capabilityScores: RuntimeDashboardCapabilityScore[];
	hardware: RuntimeDashboardHardware;
	engineRecommendations: RuntimeDashboardEngineRecommendation[];
	premiumFeatures: RuntimeDashboardPremiumFeature[];
	timeline: RuntimeDashboardTimelineEvent[];
	warnings: RuntimeDashboardWarning[];
	recommendations: RuntimeDashboardRecommendations;
	shadowCommentary: RuntimeDashboardShadowCommentary;
	maturity: RuntimeCapabilityMaturityOverview;
}

export interface RuntimeCapabilitySelectionView {
	capability: string;
	label: string;
	videoPipeline?: boolean;
	referenceEngineId?: string | null;
	referenceDisplayName?: string | null;
	recommendedEngineId?: string | null;
	recommendedDisplayName?: string | null;
	currentEngineId?: string | null;
	currentDisplayName?: string | null;
	installedEngineIds?: string[];
	adapterKey?: string | null;
	blockedReason?: string | null;
	blocked?: boolean;
	executable?: boolean;
	health?: string | null;
	resolvedEngine?: Record<string, unknown>;
}
