import type {
	InviteMemberInput,
	UpdateMemberRoleInput,
	WorkspaceInvitation,
	WorkspaceMember,
} from "./types";

export interface CollaborationRepository {
	listMembers(workspaceId: string): Promise<WorkspaceMember[]>;
	inviteMember(
		workspaceId: string,
		input: InviteMemberInput,
	): Promise<WorkspaceInvitation>;
	updateMemberRole(
		workspaceId: string,
		memberId: string,
		input: UpdateMemberRoleInput,
	): Promise<WorkspaceMember>;
	removeMember(workspaceId: string, memberId: string): Promise<void>;
	listInvitations(workspaceId: string): Promise<WorkspaceInvitation[]>;
}
