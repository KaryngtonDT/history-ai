import type { ReactNode } from "react";
import styles from "./CollapsibleSection.module.css";

interface CollapsibleSectionProps {
	title: string;
	children: ReactNode;
	defaultOpen?: boolean;
}

export function CollapsibleSection({
	title,
	children,
	defaultOpen = false,
}: CollapsibleSectionProps) {
	return (
		<details className={styles.root} open={defaultOpen || undefined}>
			<summary className={styles.summary}>{title}</summary>
			<div className={styles.content}>{children}</div>
		</details>
	);
}
