import type { ShadowRelationshipRepository } from "./ShadowRelationshipRepository";
import type {
	RelationshipConfigureResult,
	RelationshipPortrait,
	RelationshipProfile,
} from "./types";

const defaultProfile: RelationshipProfile = {
	id: "11111111-1111-4111-8111-111111111111",
	scopeKey: "default",
	relationshipScore: 72,
	preferences: {
		adaptiveEnabled: true,
		rememberRelationship: true,
		requireApprovalForInferences: true,
	},
	settings: {
		showHypotheses: true,
		showTimeline: true,
		allowConversationalUpdates: true,
	},
	traits: [
		{
			type: "interest",
			key: "programming",
			label: "Programming",
			strength: "very_high",
			source: "user",
			confirmed: true,
			enabled: true,
			explanation: "User learns mainly through software topics.",
		},
		{
			type: "communication",
			key: "storyteller",
			label: "Storyteller",
			strength: "high",
			source: "signal",
			confirmed: false,
			enabled: true,
			explanation: "Story mode sessions retain attention longer.",
		},
	],
	timeline: [
		{
			id: "t1",
			type: "started_topic",
			label: "Started learning English",
			detail: "First English-focused sessions.",
			recordedAt: "2026-04-01T10:00:00+00:00",
		},
		{
			id: "t2",
			type: "communication_shift",
			label: "Prefers storyteller voice",
			detail: "Story narration mode selected repeatedly.",
			recordedAt: "2026-06-01T10:00:00+00:00",
		},
	],
	sharedReferences: [
		{
			id: "r1",
			kind: "topic",
			label: "Symfony microservices discussion",
			detail: "Referenced during Kubernetes explanation.",
			recordedAt: "2026-05-15T10:00:00+00:00",
		},
	],
	pendingChanges: [],
};

const defaultPortrait: RelationshipPortrait = {
	relationshipScore: 72,
	confirmed: defaultProfile.traits.filter((trait) => trait.confirmed),
	hypotheses: defaultProfile.traits.filter((trait) => !trait.confirmed),
	questions: [
		{
			id: "session_length",
			text: "Do you prefer short or long learning sessions?",
		},
	],
	pendingChanges: [],
};

export class MockShadowRelationshipRepository
	implements ShadowRelationshipRepository
{
	private profile = structuredClone(defaultProfile);

	getProfile(): Promise<RelationshipProfile> {
		return Promise.resolve(structuredClone(this.profile));
	}

	getPortrait(): Promise<RelationshipPortrait> {
		return Promise.resolve(structuredClone(defaultPortrait));
	}

	getTimeline() {
		return Promise.resolve({
			scopeKey: "default",
			timeline: structuredClone(this.profile.timeline),
		});
	}

	getInterests() {
		return Promise.resolve({
			scopeKey: "default",
			interests: this.profile.traits
				.filter((trait) => trait.type === "interest")
				.map((trait) => ({
					key: trait.key,
					label: trait.label,
					strength: trait.strength,
					confirmed: trait.confirmed,
				})),
		});
	}

	recordSignal() {
		return this.getProfile();
	}

	updatePreferences(request) {
		if (typeof request.adaptiveEnabled === "boolean") {
			this.profile.preferences.adaptiveEnabled = request.adaptiveEnabled;
		}

		return this.getProfile();
	}

	reset() {
		this.profile = structuredClone(defaultProfile);

		return this.getProfile();
	}

	configure(
		utterance: string,
		confirmed = false,
	): Promise<RelationshipConfigureResult> {
		const requiresConfirmation = utterance.toLowerCase().includes("football");

		return Promise.resolve({
			intent: requiresConfirmation ? "remember_habit" : "unknown",
			previewLabel: requiresConfirmation
				? "Explain with football analogies"
				: "",
			requiresConfirmation: requiresConfirmation && !confirmed,
			confirmationMessage: requiresConfirmation
				? "Apply football analogies in explanations?"
				: "Could not map request yet.",
			applied: requiresConfirmation && confirmed,
			profile: structuredClone(this.profile),
			portrait: structuredClone(defaultPortrait),
		});
	}

	approveChange(changeId: string) {
		this.profile.pendingChanges = this.profile.pendingChanges.filter(
			(change) => change.id !== changeId,
		);

		return Promise.resolve({
			profile: structuredClone(this.profile),
			portrait: structuredClone(defaultPortrait),
		});
	}

	rejectChange(changeId: string) {
		return this.approveChange(changeId);
	}
}
