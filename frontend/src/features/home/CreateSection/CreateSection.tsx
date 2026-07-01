import { Link } from "react-router";
import { useTranslation } from "@/i18n";
import { CREATE_CARDS } from "../createCards";
import styles from "./CreateSection.module.css";

export function CreateSection() {
	const { t } = useTranslation();

	return (
		<section className={styles.root} aria-labelledby="create-heading">
			<h2 id="create-heading" className={styles.heading}>
				{t("home.create.heading")}
			</h2>
			<div className={styles.grid}>
				{CREATE_CARDS.map((card) => {
					const label = t(`home.create.${card.id}.label`);
					const description = t(`home.create.${card.id}.description`);
					const nextStep = t(`home.create.${card.id}.nextStep`);

					return card.comingSoon ? (
						<div key={card.id} className={styles.card}>
							<span className={styles.icon} aria-hidden="true">
								{card.icon}
							</span>
							<span className={styles.label}>{label}</span>
							<span className={styles.description}>{description}</span>
							<span className={styles.nextStep}>{t("common.comingSoon")}</span>
						</div>
					) : (
						<Link
							key={card.id}
							to={card.route}
							aria-label={t("home.create.ariaLabel", { type: label })}
							className={
								card.primary
									? `${styles.card} ${styles.cardPrimary}`
									: styles.card
							}
						>
							<span className={styles.icon} aria-hidden="true">
								{card.icon}
							</span>
							<span className={styles.label}>{label}</span>
							<span className={styles.description}>{description}</span>
							<span className={styles.nextStep}>
								{t("home.create.nextPrefix")} {nextStep}
							</span>
						</Link>
					);
				})}
			</div>
		</section>
	);
}
