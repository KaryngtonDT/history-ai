import { useTranslation } from "@/i18n";
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
	label,
}: RoleSelectorProps) {
	const { t } = useTranslation();
	const resolvedLabel = label ?? t("workspace.team.roleLabel");

	return (
		<label className={styles.root}>
			<span className={styles.label}>{resolvedLabel}</span>
			<select
				className={styles.select}
				value={value}
				disabled={disabled}
				onChange={(event) => onChange(event.target.value as WorkspaceRole)}
				aria-label={resolvedLabel}
			>
				{options.map((role) => (
					<option key={role} value={role}>
						{t(`workspace.team.roles.${role}`)}
					</option>
				))}
			</select>
		</label>
	);
}
