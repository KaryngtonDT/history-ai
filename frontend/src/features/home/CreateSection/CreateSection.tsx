import { Link } from "react-router";
import { CREATE_CARDS } from "../createCards";
import styles from "./CreateSection.module.css";

export function CreateSection() {
	return (
		<section className={styles.root} aria-labelledby="create-heading">
			<h2 id="create-heading" className={styles.heading}>
				What do you want to transform?
			</h2>
			<div className={styles.grid}>
				{CREATE_CARDS.map((card) =>
					card.comingSoon ? (
						<div key={card.id} className={styles.card}>
							<span className={styles.icon} aria-hidden="true">
								{card.icon}
							</span>
							<span className={styles.label}>{card.label}</span>
							<span className={styles.description}>{card.description}</span>
							<span className={styles.nextStep}>Coming soon</span>
						</div>
					) : (
						<Link
							key={card.id}
							to={card.route}
							aria-label={`Create ${card.label}`}
							className={
								card.primary
									? `${styles.card} ${styles.cardPrimary}`
									: styles.card
							}
						>
							<span className={styles.icon} aria-hidden="true">
								{card.icon}
							</span>
							<span className={styles.label}>{card.label}</span>
							<span className={styles.description}>{card.description}</span>
							<span className={styles.nextStep}>Next: {card.nextStep}</span>
						</Link>
					),
				)}
			</div>
		</section>
	);
}
