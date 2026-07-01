import type {
	ShadowInterventionFrequency,
	ShadowInterventionPolicy,
	ShadowTutorMode,
} from "@/services/shadow/types";

export function tutorModeFromPolicy(
	policy: ShadowInterventionPolicy,
): ShadowTutorMode {
	if (!policy.enabled) {
		return "off";
	}

	if (
		policy.challengeLevel === "easy" &&
		policy.maxInterventionsPerMinute <= 2
	) {
		return "gentle";
	}

	return "normal";
}

export function frequencyFromPolicy(
	policy: ShadowInterventionPolicy,
): ShadowInterventionFrequency {
	if (policy.maxInterventionsPerMinute <= 1) {
		return "low";
	}

	if (policy.maxInterventionsPerMinute >= 4) {
		return "high";
	}

	return "normal";
}

export function policyWithFrequency(
	policy: ShadowInterventionPolicy,
	frequency: ShadowInterventionFrequency,
): ShadowInterventionPolicy {
	const values = {
		low: { maxInterventionsPerMinute: 1, minSecondsBetweenInterventions: 60 },
		normal: {
			maxInterventionsPerMinute: 2,
			minSecondsBetweenInterventions: 45,
		},
		high: { maxInterventionsPerMinute: 4, minSecondsBetweenInterventions: 30 },
	} as const;

	return {
		...policy,
		...values[frequency],
	};
}
