import { useState } from "react";
import { Button } from "@/components/ui/Button";
import type { FeatureHelpId } from "../content/features";
import { FeatureAcademy } from "../FeatureAcademy";
import styles from "./ExplainThisButton.module.css";

interface ExplainThisButtonProps {
	featureId: FeatureHelpId;
	label?: string;
}

export function ExplainThisButton({
	featureId,
	label = "Explain this",
}: ExplainThisButtonProps) {
	const [open, setOpen] = useState(false);

	return (
		<>
			<Button variant="secondary" size="sm" onClick={() => setOpen(true)}>
				{label}
			</Button>
			{open ? (
				<div
					className={styles.overlay}
					role="dialog"
					aria-modal="true"
					aria-label="Feature explanation"
				>
					<FeatureAcademy
						featureId={featureId}
						onClose={() => setOpen(false)}
					/>
				</div>
			) : null}
		</>
	);
}
