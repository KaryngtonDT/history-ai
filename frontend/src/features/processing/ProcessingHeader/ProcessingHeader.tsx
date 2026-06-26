import styles from "./ProcessingHeader.module.css";

interface ProcessingHeaderProps {
	title: string;
}

export function ProcessingHeader({ title }: ProcessingHeaderProps) {
	return (
		<header className={styles.header}>
			<h2 className={styles.heading}>Processing</h2>
			<p className={styles.title}>{title}</p>
		</header>
	);
}
