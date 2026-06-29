import { useNavigate } from "react-router";
import { Button } from "@/components/ui/Button";
import styles from "./QuickActions.module.css";

const actions = [
	{ label: "Import PDF", path: "/import" },
	{ label: "Import Audio", path: "/import" },
	{ label: "Import Video", path: "/video/upload" },
] as const;

export function QuickActions() {
	const navigate = useNavigate();

	return (
		<div className={styles.root}>
			{actions.map(({ label, path }) => (
				<Button
					key={label}
					variant="secondary"
					size="md"
					onClick={() => navigate(path)}
				>
					{label}
				</Button>
			))}
		</div>
	);
}
