import {
	workspaceInvitationsPath,
	workspaceMemberPath,
	workspaceMembersPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { CollaborationRepository } from "./CollaborationRepository";
import type {
	InviteMemberInput,
	UpdateMemberRoleInput,
	WorkspaceInvitationApiDto,
	WorkspaceMemberApiDto,
} from "./types";
import { mapInvitationFromApi, mapMemberFromApi } from "./types";

export class HttpCollaborationRepository implements CollaborationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listMembers(workspaceId: string) {
		const members = await this.httpClient.get<WorkspaceMemberApiDto[]>(
			workspaceMembersPath(workspaceId),
		);

		return members.map(mapMemberFromApi);
	}

	async inviteMember(workspaceId: string, input: InviteMemberInput) {
		const invitation = await this.httpClient.post<WorkspaceInvitationApiDto>(
			workspaceMembersPath(workspaceId),
			input,
		);

		return mapInvitationFromApi(invitation);
	}

	async updateMemberRole(
		workspaceId: string,
		memberId: string,
		input: UpdateMemberRoleInput,
	) {
		const member = await this.httpClient.patch<WorkspaceMemberApiDto>(
			workspaceMemberPath(workspaceId, memberId),
			input,
		);

		return mapMemberFromApi(member);
	}

	async removeMember(workspaceId: string, memberId: string): Promise<void> {
		await this.httpClient.delete(workspaceMemberPath(workspaceId, memberId));
	}

	async listInvitations(workspaceId: string) {
		const invitations = await this.httpClient.get<WorkspaceInvitationApiDto[]>(
			workspaceInvitationsPath(workspaceId),
		);

		return invitations.map(mapInvitationFromApi);
	}
}
