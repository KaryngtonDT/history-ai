import { APP } from "@/config/app";
import styles from "./DashboardHeader.module.css";

export function DashboardHeader() {
	return (
		<header className={styles.header}>
			<h2 className={styles.title}>{APP.NAME}</h2>
			<p className={styles.tagline}>{APP.TAGLINE}</p>
		</header>
	);
}
