import { collaborationService } from "@/services/collaboration/CollaborationService";
import type { WorkspaceInvitation } from "@/services/collaboration/types";
import styles from "./InvitationList.module.css";

interface InvitationListProps {
	invitations: WorkspaceInvitation[];
}

export function InvitationList({ invitations }: InvitationListProps) {
	if (invitations.length === 0) {
		return null;
	}

	return (
		<div className={styles.root}>
			<h3 className={styles.title}>Pending invitations</h3>
			<ul className={styles.list}>
				{invitations.map((invitation) => (
					<li key={invitation.id} className={styles.item}>
						<span>{invitation.email}</span>
						<span className={styles.role}>
							{collaborationService.formatRole(invitation.role)}
						</span>
					</li>
				))}
			</ul>
		</div>
	);
}
