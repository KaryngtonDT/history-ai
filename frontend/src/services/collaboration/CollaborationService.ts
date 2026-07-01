import type { CollaborationRepository } from "./CollaborationRepository";
import { createCollaborationRepository } from "./CollaborationRepositoryFactory";
import type {
	InviteMemberInput,
	UpdateMemberRoleInput,
	WorkspaceInvitation,
	WorkspaceMember,
	WorkspaceRole,
} from "./types";
import { INVITABLE_ROLES, WORKSPACE_ROLE_LABELS } from "./types";

export class CollaborationService {
	private readonly repository: CollaborationRepository;

	constructor(repository: CollaborationRepository) {
		this.repository = repository;
	}

	listMembers(workspaceId: string): Promise<WorkspaceMember[]> {
		return this.repository.listMembers(workspaceId);
	}

	inviteMember(
		workspaceId: string,
		input: InviteMemberInput,
	): Promise<WorkspaceInvitation> {
		return this.repository.inviteMember(workspaceId, input);
	}

	updateMemberRole(
		workspaceId: string,
		memberId: string,
		input: UpdateMemberRoleInput,
	): Promise<WorkspaceMember> {
		return this.repository.updateMemberRole(workspaceId, memberId, input);
	}

	removeMember(workspaceId: string, memberId: string): Promise<void> {
		return this.repository.removeMember(workspaceId, memberId);
	}

	listInvitations(workspaceId: string): Promise<WorkspaceInvitation[]> {
		return this.repository.listInvitations(workspaceId);
	}

	formatRole(role: WorkspaceRole): string {
		return WORKSPACE_ROLE_LABELS[role];
	}

	invitableRoles(): WorkspaceRole[] {
		return [...INVITABLE_ROLES];
	}

	sortedMembers(members: WorkspaceMember[]): WorkspaceMember[] {
		const order: WorkspaceRole[] = ["owner", "editor", "reviewer", "viewer"];

		return [...members].sort(
			(left, right) => order.indexOf(left.role) - order.indexOf(right.role),
		);
	}
}

export const collaborationService = new CollaborationService(
	createCollaborationRepository(),
);
