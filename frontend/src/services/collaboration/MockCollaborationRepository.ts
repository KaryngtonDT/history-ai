import type { CollaborationRepository } from "./CollaborationRepository";
import type {
	InviteMemberInput,
	UpdateMemberRoleInput,
	WorkspaceInvitation,
	WorkspaceMember,
	WorkspaceRole,
} from "./types";

const MOCK_MEMBERS: WorkspaceMember[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655480001",
		workspaceId: "550e8400-e29b-41d4-a716-446655450001",
		userId: "alice",
		displayName: "Alice",
		role: "owner",
		joinedAt: "2026-06-01T10:00:00+00:00",
	},
	{
		id: "550e8400-e29b-41d4-a716-446655480002",
		workspaceId: "550e8400-e29b-41d4-a716-446655450001",
		userId: "bob",
		displayName: "Bob",
		role: "editor",
		joinedAt: "2026-06-02T10:00:00+00:00",
	},
];

const MOCK_INVITATIONS: WorkspaceInvitation[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655480010",
		workspaceId: "550e8400-e29b-41d4-a716-446655450001",
		email: "charlie@example.com",
		role: "reviewer",
		status: "pending",
		token: "mock-token",
		createdAt: "2026-06-03T10:00:00+00:00",
		expiresAt: "2026-06-10T10:00:00+00:00",
	},
];

export class MockCollaborationRepository implements CollaborationRepository {
	private readonly members = new Map<string, WorkspaceMember[]>();
	private readonly invitations = new Map<string, WorkspaceInvitation[]>();

	constructor() {
		this.members.set(
			"550e8400-e29b-41d4-a716-446655450001",
			MOCK_MEMBERS.map((member) => ({ ...member })),
		);
		this.invitations.set(
			"550e8400-e29b-41d4-a716-446655450001",
			MOCK_INVITATIONS.map((invitation) => ({ ...invitation })),
		);
	}

	async listMembers(workspaceId: string): Promise<WorkspaceMember[]> {
		return (this.members.get(workspaceId) ?? []).map((member) => ({
			...member,
		}));
	}

	async inviteMember(
		workspaceId: string,
		input: InviteMemberInput,
	): Promise<WorkspaceInvitation> {
		const invitation: WorkspaceInvitation = {
			id: crypto.randomUUID(),
			workspaceId,
			email: input.email,
			role: input.role,
			status: "pending",
			token: `mock-${input.email}`,
			createdAt: new Date().toISOString(),
			expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
		};

		const existing = this.invitations.get(workspaceId) ?? [];
		this.invitations.set(workspaceId, [...existing, invitation]);

		return invitation;
	}

	async updateMemberRole(
		workspaceId: string,
		memberId: string,
		input: UpdateMemberRoleInput,
	): Promise<WorkspaceMember> {
		const members = this.members.get(workspaceId) ?? [];
		const updated = members.map((member) =>
			member.id === memberId ? { ...member, role: input.role } : member,
		);
		this.members.set(workspaceId, updated);

		const member = updated.find((entry) => entry.id === memberId);

		if (!member) {
			throw new Error("Member not found");
		}

		return member;
	}

	async removeMember(workspaceId: string, memberId: string): Promise<void> {
		const members = this.members.get(workspaceId) ?? [];
		this.members.set(
			workspaceId,
			members.filter((member) => member.id !== memberId),
		);
	}

	async listInvitations(workspaceId: string): Promise<WorkspaceInvitation[]> {
		return (this.invitations.get(workspaceId) ?? []).map((invitation) => ({
			...invitation,
		}));
	}

	formatRole(role: WorkspaceRole): string {
		return role.charAt(0).toUpperCase() + role.slice(1);
	}
}
