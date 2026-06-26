import styles from "./LibraryHeader.module.css";

export function LibraryHeader() {
	return (
		<header className={styles.header}>
			<h2 className={styles.title}>Library</h2>
			<p className={styles.description}>
				Your imported knowledge sources and learning materials.
			</p>
		</header>
	);
}
