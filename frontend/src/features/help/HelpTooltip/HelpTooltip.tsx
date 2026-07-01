import { useId, useState } from "react";
import { useTranslation } from "@/i18n";
import type { FeatureHelpId } from "../content/features";
import { getFeatureHelp } from "../content/features";
import styles from "./HelpTooltip.module.css";

interface HelpTooltipProps {
	featureId: FeatureHelpId;
	label?: string;
}

export function HelpTooltip({ featureId, label }: HelpTooltipProps) {
	const { t } = useTranslation();
	const [open, setOpen] = useState(false);
	const tooltipId = useId();
	const help = getFeatureHelp(featureId);
	const triggerLabel = label ?? t("help.tooltip.defaultLabel");

	return (
		<span className={styles.wrapper}>
			<button
				type="button"
				className={styles.tooltip}
				aria-label={t("help.tooltip.ariaLabel", { title: help.title })}
				aria-describedby={open ? tooltipId : undefined}
				onMouseEnter={() => setOpen(true)}
				onMouseLeave={() => setOpen(false)}
				onFocus={() => setOpen(true)}
				onBlur={() => setOpen(false)}
			>
				{triggerLabel}
			</button>
			{open ? (
				<span id={tooltipId} role="tooltip" className={styles.panel}>
					<strong>{help.title}</strong>
					<br />
					{help.short}
				</span>
			) : null}
		</span>
	);
}
