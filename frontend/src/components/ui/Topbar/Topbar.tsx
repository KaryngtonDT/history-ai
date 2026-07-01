import { APP } from "@/config/app";
import styles from "./Topbar.module.css";

export function Topbar() {
	return (
		<header className={styles.header}>
			<h1 className={styles.title}>{APP.NAME}</h1>
			<p className={styles.subtitle}>{APP.TAGLINE}</p>
		</header>
	);
}
