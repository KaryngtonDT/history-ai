import type { ReactNode } from "react";
import styles from "./CreatePageLayout.module.css";

interface CreatePageLayoutProps {
	primary: ReactNode;
	secondary: ReactNode;
}

export function CreatePageLayout({
	primary,
	secondary,
}: CreatePageLayoutProps) {
	return (
		<div className={styles.root}>
			<div className={styles.primary}>{primary}</div>
			<aside className={styles.secondary} aria-label="Guidance and previews">
				{secondary}
			</aside>
		</div>
	);
}
