import { useCallback, useEffect, useState } from "react";
import { collaborationService } from "@/services/collaboration/CollaborationService";
import type {
	WorkspaceInvitation,
	WorkspaceMember,
	WorkspaceRole,
} from "@/services/collaboration/types";
import { InvitationList } from "../InvitationList";
import { RoleSelector } from "../RoleSelector";
import { WorkspaceMembers } from "../WorkspaceMembers";
import styles from "./TeamPanel.module.css";

interface TeamPanelProps {
	workspaceId: string | null;
}

export function TeamPanel({ workspaceId }: TeamPanelProps) {
	const [members, setMembers] = useState<WorkspaceMember[]>([]);
	const [invitations, setInvitations] = useState<WorkspaceInvitation[]>([]);
	const [email, setEmail] = useState("");
	const [role, setRole] = useState<WorkspaceRole>("editor");
	const [busy, setBusy] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const loadTeam = useCallback(async (id: string) => {
		setError(null);

		const [loadedMembers, loadedInvitations] = await Promise.all([
			collaborationService.listMembers(id),
			collaborationService.listInvitations(id),
		]);

		setMembers(loadedMembers);
		setInvitations(loadedInvitations);
	}, []);

	useEffect(() => {
		if (!workspaceId) {
			setMembers([]);
			setInvitations([]);
			return;
		}

		void loadTeam(workspaceId).catch(() => {
			setError("Could not load team members.");
		});
	}, [workspaceId, loadTeam]);

	const handleInvite = (): void => {
		if (!workspaceId || email.trim() === "") {
			return;
		}

		setBusy(true);
		setError(null);

		void collaborationService
			.inviteMember(workspaceId, { email: email.trim(), role })
			.then(() => {
				setEmail("");
				return loadTeam(workspaceId);
			})
			.catch(() => {
				setError("Could not send invitation.");
			})
			.finally(() => {
				setBusy(false);
			});
	};

	const handleRoleChange = (
		memberId: string,
		nextRole: WorkspaceRole,
	): void => {
		if (!workspaceId) {
			return;
		}

		setBusy(true);
		setError(null);

		void collaborationService
			.updateMemberRole(workspaceId, memberId, { role: nextRole })
			.then(() => loadTeam(workspaceId))
			.catch(() => {
				setError("Could not update member role.");
			})
			.finally(() => {
				setBusy(false);
			});
	};

	const handleRemove = (memberId: string): void => {
		if (!workspaceId) {
			return;
		}

		setBusy(true);
		setError(null);

		void collaborationService
			.removeMember(workspaceId, memberId)
			.then(() => loadTeam(workspaceId))
			.catch(() => {
				setError("Could not remove member.");
			})
			.finally(() => {
				setBusy(false);
			});
	};

	if (!workspaceId) {
		return null;
	}

	return (
		<section className={styles.root}>
			<div className={styles.header}>
				<div>
					<p className={styles.eyebrow}>Team</p>
					<h2 className={styles.title}>Members</h2>
				</div>
			</div>

			<WorkspaceMembers
				members={members}
				busy={busy}
				onRoleChange={handleRoleChange}
				onRemove={handleRemove}
			/>

			<InvitationList invitations={invitations} />

			<div className={styles.inviteForm}>
				<h3 className={styles.inviteTitle}>Invite member</h3>
				<input
					type="email"
					value={email}
					onChange={(event) => setEmail(event.target.value)}
					placeholder="Email address"
					className={styles.input}
					aria-label="Member email"
				/>
				<RoleSelector
					value={role}
					options={collaborationService.invitableRoles()}
					disabled={busy}
					onChange={setRole}
				/>
				<button
					type="button"
					className={styles.primaryButton}
					disabled={busy || email.trim() === ""}
					onClick={handleInvite}
				>
					Send invitation
				</button>
			</div>

			{error ? <p className={styles.error}>{error}</p> : null}
		</section>
	);
}
