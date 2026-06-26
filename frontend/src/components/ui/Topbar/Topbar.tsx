import styles from "./Topbar.module.css";

export function Topbar() {
	return (
		<header className={styles.header}>
			<h1 className={styles.title}>History AI</h1>
			<p className={styles.subtitle}>Knowledge Operating System</p>
		</header>
	);
}
