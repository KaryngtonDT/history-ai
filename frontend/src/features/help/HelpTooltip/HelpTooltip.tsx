import { useId, useState } from "react";
import type { FeatureHelpId } from "../content/features";
import { getFeatureHelp } from "../content/features";
import styles from "./HelpTooltip.module.css";

interface HelpTooltipProps {
	featureId: FeatureHelpId;
	label?: string;
}

export function HelpTooltip({ featureId, label = "?" }: HelpTooltipProps) {
	const [open, setOpen] = useState(false);
	const tooltipId = useId();
	const help = getFeatureHelp(featureId);

	return (
		<span className={styles.wrapper}>
			<button
				type="button"
				className={styles.tooltip}
				aria-label={`Help: ${help.title}`}
				aria-describedby={open ? tooltipId : undefined}
				onMouseEnter={() => setOpen(true)}
				onMouseLeave={() => setOpen(false)}
				onFocus={() => setOpen(true)}
				onBlur={() => setOpen(false)}
			>
				{label}
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
