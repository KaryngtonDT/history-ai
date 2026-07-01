import { useState } from "react";
import { Button } from "@/components/ui/Button";
import { useTranslation } from "@/i18n";
import type { FeatureHelpId } from "../content/features";
import { FeatureAcademy } from "../FeatureAcademy";
import styles from "./ExplainThisButton.module.css";

interface ExplainThisButtonProps {
	featureId: FeatureHelpId;
	label?: string;
}

export function ExplainThisButton({
	featureId,
	label,
}: ExplainThisButtonProps) {
	const { t } = useTranslation();
	const [open, setOpen] = useState(false);
	const buttonLabel = label ?? t("help.explain.defaultLabel");

	return (
		<>
			<Button variant="secondary" size="sm" onClick={() => setOpen(true)}>
				{buttonLabel}
			</Button>
			{open ? (
				<div
					className={styles.overlay}
					role="dialog"
					aria-modal="true"
					aria-label={t("help.explain.dialogAria")}
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
