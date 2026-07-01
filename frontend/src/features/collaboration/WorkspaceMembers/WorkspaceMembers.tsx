import { collaborationService } from "@/services/collaboration/CollaborationService";
import type {
	WorkspaceMember,
	WorkspaceRole,
} from "@/services/collaboration/types";
import { RoleSelector } from "../RoleSelector";
import styles from "./WorkspaceMembers.module.css";

interface WorkspaceMembersProps {
	members: WorkspaceMember[];
	onRoleChange: (memberId: string, role: WorkspaceRole) => void;
	onRemove: (memberId: string) => void;
	busy?: boolean;
}

export function WorkspaceMembers({
	members,
	onRoleChange,
	onRemove,
	busy = false,
}: WorkspaceMembersProps) {
	if (members.length === 0) {
		return <p className={styles.empty}>No members yet.</p>;
	}

	return (
		<ul className={styles.list}>
			{collaborationService.sortedMembers(members).map((member) => (
				<li key={member.id} className={styles.item}>
					<div className={styles.identity}>
						<span className={styles.avatar} aria-hidden="true">
							👤
						</span>
						<div>
							<p className={styles.name}>{member.displayName}</p>
							<p className={styles.userId}>{member.userId}</p>
						</div>
					</div>
					<div className={styles.actions}>
						{member.role === "owner" ? (
							<span className={styles.roleBadge}>
								{collaborationService.formatRole(member.role)}
							</span>
						) : (
							<RoleSelector
								value={member.role}
								options={collaborationService.invitableRoles()}
								disabled={busy}
								onChange={(role) => onRoleChange(member.id, role)}
							/>
						)}
						{member.role !== "owner" ? (
							<button
								type="button"
								className={styles.removeButton}
								disabled={busy}
								onClick={() => onRemove(member.id)}
							>
								Remove
							</button>
						) : null}
					</div>
				</li>
			))}
		</ul>
	);
}
