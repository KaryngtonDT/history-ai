import styles from "./DashboardHeader.module.css";

export function DashboardHeader() {
	return (
		<header className={styles.header}>
			<h2 className={styles.title}>History AI</h2>
			<p className={styles.tagline}>Transform knowledge into understanding.</p>
		</header>
	);
}
