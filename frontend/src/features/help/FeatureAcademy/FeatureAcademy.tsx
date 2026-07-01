import { Button } from "@/components/ui/Button";
import { useTranslation } from "@/i18n";
import type { FeatureHelpId } from "../content/features";
import { getFeatureHelp } from "../content/features";
import styles from "./FeatureAcademy.module.css";

interface FeatureAcademyProps {
	featureId: FeatureHelpId;
	onClose?: () => void;
}

export function FeatureAcademy({ featureId, onClose }: FeatureAcademyProps) {
	const { t } = useTranslation();
	const help = getFeatureHelp(featureId);

	return (
		<article className={styles.panel}>
			<h2 className={styles.title}>{help.title}</h2>
			<p className={styles.meta}>
				{t("help.academy.readingTime", { minutes: help.readingMinutes })}
			</p>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>
					{t("help.academy.sections.whatIsIt")}
				</h3>
				<p className={styles.sectionBody}>{help.short}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>
					{t("help.academy.sections.details")}
				</h3>
				<p className={styles.sectionBody}>{help.details}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>
					{t("help.academy.sections.bestPractice")}
				</h3>
				<p className={styles.sectionBody}>{help.bestPractice}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>
					{t("help.academy.sections.commonMistake")}
				</h3>
				<p className={styles.sectionBody}>{help.commonMistake}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>
					{t("help.academy.sections.nextStep")}
				</h3>
				<p className={styles.sectionBody}>{help.nextStep}</p>
			</section>

			{help.faq.length > 0 ? (
				<section className={styles.section}>
					<h3 className={styles.sectionTitle}>
						{t("help.academy.sections.faq")}
					</h3>
					{help.faq.map((entry) => (
						<div key={entry.question} className={styles.faqItem}>
							<p className={styles.faqQuestion}>{entry.question}</p>
							<p className={styles.faqAnswer}>{entry.answer}</p>
						</div>
					))}
				</section>
			) : null}

			{onClose ? (
				<div className={styles.close}>
					<Button variant="secondary" onClick={onClose}>
						{t("common.close")}
					</Button>
				</div>
			) : null}
		</article>
	);
}
