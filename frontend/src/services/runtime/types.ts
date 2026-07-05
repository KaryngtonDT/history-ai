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

export interface RuntimeEngine {
	id: string;
	displayName: string;
	capability: string;
	status: string;
	mode: string;
	role?: string | null;
	roleLabel?: string | null;
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
