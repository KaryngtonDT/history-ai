import type { WorkspaceRole } from "@/services/collaboration/types";
import styles from "./RoleSelector.module.css";

interface RoleSelectorProps {
	value: WorkspaceRole;
	options: WorkspaceRole[];
	onChange: (role: WorkspaceRole) => void;
	disabled?: boolean;
	label?: string;
}

export function RoleSelector({
	value,
	options,
	onChange,
	disabled = false,
	label = "Role",
}: RoleSelectorProps) {
	return (
		<label className={styles.root}>
			<span className={styles.label}>{label}</span>
			<select
				className={styles.select}
				value={value}
				disabled={disabled}
				onChange={(event) => onChange(event.target.value as WorkspaceRole)}
				aria-label={label}
			>
				{options.map((role) => (
					<option key={role} value={role}>
						{role.charAt(0).toUpperCase() + role.slice(1)}
					</option>
				))}
			</select>
		</label>
	);
}
