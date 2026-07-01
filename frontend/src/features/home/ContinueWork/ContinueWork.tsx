import { Link } from "react-router";
import styles from "./ContinueWork.module.css";

interface ContinueWorkProps {
	item: import("@/services/workItem/types").WorkItem;
}

export function ContinueWork({ item }: ContinueWorkProps) {
	return (
		<section className={styles.root} aria-labelledby="continue-work-heading">
			<h2 id="continue-work-heading" className={styles.heading}>
				Continue your work
			</h2>
			<div className={styles.card}>
				<div className={styles.header}>
					<span className={styles.icon} aria-hidden="true">
						{item.icon}
					</span>
					<div>
						<p className={styles.title}>{item.title}</p>
						<p className={styles.meta}>
							{item.type} · {item.status}
						</p>
					</div>
				</div>
				<p className={styles.step}>Current step: {item.currentStep}</p>
				<Link to={item.primaryActionRoute} className={styles.resumeLink}>
					{item.primaryActionLabel} →
				</Link>
			</div>
		</section>
	);
}
