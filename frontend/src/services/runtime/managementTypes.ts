export type CapabilitySelectionMode = "auto" | "manual" | "locked";

export interface RuntimeManagedEngine {
	engineId: string;
	displayName: string;
	capability: string;
	capabilityLabel: string;
	provider: string;
	installed: boolean;
	ready: boolean;
	blocked: boolean;
	misconfigured: boolean;
	mock: boolean;
	status: string;
	mode: string;
	isReference: boolean;
	isRecommended: boolean;
	isCurrent: boolean;
	benchmarkScore?: string | null;
	averageDurationSeconds?: number | null;
	averageAccuracy?: number | null;
	executionCount?: number;
	blockedReason?: string | null;
	documentationUrl?: string | null;
	documentationPath?: string | null;
	installCommand?: string | null;
	autoProvisionSupported?: boolean;
}

export type CapabilityClassification =
	| "core"
	| "optional"
	| "premium"
	| "experimental"
	| "deprecated";

export interface RuntimeManagedCapability {
	capability: string;
	label: string;
	classification?: CapabilityClassification;
	classificationLabel?: string;
	required?: boolean;
	enabledByDefault?: boolean;
	hardwareDependent?: boolean;
	installable?: boolean;
	recommended?: boolean;
	videoPipeline: boolean;
	selectionMode: CapabilitySelectionMode;
	selectionModeLabel: string;
	recommendedEngineId?: string | null;
	currentEngineId?: string | null;
	referenceEngineId?: string | null;
	engines: RuntimeManagedEngine[];
}

export interface RuntimeEngineManagement {
	principle: string;
	configuration: Record<string, unknown>;
	recommendations: Array<Record<string, unknown>>;
	capabilities: RuntimeManagedCapability[];
	at: string;
}

export interface RuntimeSelectionUpdate {
	selectionMode?: string;
	manualSelections?: Record<string, string>;
	capabilityModes?: Record<string, CapabilitySelectionMode>;
	lockedSelections?: Record<string, string>;
}
