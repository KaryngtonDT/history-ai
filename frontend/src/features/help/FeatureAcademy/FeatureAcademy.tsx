import { Button } from "@/components/ui/Button";
import type { FeatureHelpId } from "../content/features";
import { getFeatureHelp } from "../content/features";
import styles from "./FeatureAcademy.module.css";

interface FeatureAcademyProps {
	featureId: FeatureHelpId;
	onClose?: () => void;
}

export function FeatureAcademy({ featureId, onClose }: FeatureAcademyProps) {
	const help = getFeatureHelp(featureId);

	return (
		<article className={styles.panel}>
			<h2 className={styles.title}>{help.title}</h2>
			<p className={styles.meta}>
				Estimated reading: {help.readingMinutes} min
			</p>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>What is it?</h3>
				<p className={styles.sectionBody}>{help.short}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>Details</h3>
				<p className={styles.sectionBody}>{help.details}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>Best practice</h3>
				<p className={styles.sectionBody}>{help.bestPractice}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>Common mistake</h3>
				<p className={styles.sectionBody}>{help.commonMistake}</p>
			</section>

			<section className={styles.section}>
				<h3 className={styles.sectionTitle}>Next step</h3>
				<p className={styles.sectionBody}>{help.nextStep}</p>
			</section>

			{help.faq.length > 0 ? (
				<section className={styles.section}>
					<h3 className={styles.sectionTitle}>FAQ</h3>
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
						Close
					</Button>
				</div>
			) : null}
		</article>
	);
}
