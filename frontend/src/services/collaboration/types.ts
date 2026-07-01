export type WorkspaceRole = "owner" | "editor" | "reviewer" | "viewer";

export interface WorkspaceMember {
	id: string;
	workspaceId: string;
	userId: string;
	displayName: string;
	role: WorkspaceRole;
	joinedAt: string;
}

export interface WorkspaceInvitation {
	id: string;
	workspaceId: string;
	email: string;
	role: WorkspaceRole;
	status: string;
	token: string;
	createdAt: string;
	expiresAt: string;
}

export interface InviteMemberInput {
	email: string;
	role: WorkspaceRole;
	displayName?: string;
}

export interface UpdateMemberRoleInput {
	role: WorkspaceRole;
}

export interface WorkspaceMemberApiDto {
	id: string;
	workspaceId: string;
	userId: string;
	displayName: string;
	role: WorkspaceRole;
	joinedAt: string;
}

export interface WorkspaceInvitationApiDto {
	id: string;
	workspaceId: string;
	email: string;
	role: WorkspaceRole;
	status: string;
	token: string;
	createdAt: string;
	expiresAt: string;
}

export const WORKSPACE_ROLE_LABELS: Record<WorkspaceRole, string> = {
	owner: "Owner",
	editor: "Editor",
	reviewer: "Reviewer",
	viewer: "Viewer",
};

export const INVITABLE_ROLES: WorkspaceRole[] = [
	"editor",
	"reviewer",
	"viewer",
];

export function mapMemberFromApi(dto: WorkspaceMemberApiDto): WorkspaceMember {
	return {
		id: dto.id,
		workspaceId: dto.workspaceId,
		userId: dto.userId,
		displayName: dto.displayName,
		role: dto.role,
		joinedAt: dto.joinedAt,
	};
}

export function mapInvitationFromApi(
	dto: WorkspaceInvitationApiDto,
): WorkspaceInvitation {
	return {
		id: dto.id,
		workspaceId: dto.workspaceId,
		email: dto.email,
		role: dto.role,
		status: dto.status,
		token: dto.token,
		createdAt: dto.createdAt,
		expiresAt: dto.expiresAt,
	};
}
