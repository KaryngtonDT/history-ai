import { useNavigate } from "react-router";
import { Button } from "@/components/ui/Button";
import styles from "./QuickActions.module.css";

const actions = [
	{ label: "Import PDF" },
	{ label: "Import Audio" },
	{ label: "Import Video" },
] as const;

export function QuickActions() {
	const navigate = useNavigate();

	return (
		<div className={styles.root}>
			{actions.map(({ label }) => (
				<Button
					key={label}
					variant="secondary"
					size="md"
					onClick={() => navigate("/import")}
				>
					{label}
				</Button>
			))}
		</div>
	);
}
