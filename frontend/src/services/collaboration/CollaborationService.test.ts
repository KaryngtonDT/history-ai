import { describe, expect, it } from "vitest";
import { CollaborationService } from "./CollaborationService";
import { MockCollaborationRepository } from "./MockCollaborationRepository";

describe("CollaborationService", () => {
	it("sorts members by role hierarchy", () => {
		const service = new CollaborationService(new MockCollaborationRepository());

		const sorted = service.sortedMembers([
			{
				id: "1",
				workspaceId: "ws",
				userId: "viewer",
				displayName: "Viewer",
				role: "viewer",
				joinedAt: "2026-06-01T00:00:00Z",
			},
			{
				id: "2",
				workspaceId: "ws",
				userId: "owner",
				displayName: "Owner",
				role: "owner",
				joinedAt: "2026-06-01T00:00:00Z",
			},
		]);

		expect(sorted[0]?.role).toBe("owner");
		expect(sorted[1]?.role).toBe("viewer");
	});
});
