import styles from "./ImportHeader.module.css";

export function ImportHeader() {
	return (
		<header className={styles.header}>
			<h2 className={styles.title}>Import</h2>
			<p className={styles.description}>
				Bring knowledge sources into History AI. Select a PDF to begin.
			</p>
		</header>
	);
}
