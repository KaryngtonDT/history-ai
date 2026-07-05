export interface RuntimeRequirement {
	key: string;
	label: string;
	satisfied: boolean;
	detail?: string | null;
}

export interface RuntimeEngine {
	id: string;
	displayName: string;
	capability: string;
	status: string;
	configured: boolean;
	discovered: boolean;
	version?: string | null;
	binaryPath?: string | null;
	requirements?: RuntimeRequirement[];
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
