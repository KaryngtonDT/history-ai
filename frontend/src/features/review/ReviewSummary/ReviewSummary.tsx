import { EmptyState } from "@/components/ui/EmptyState";
import { useTranslation } from "@/i18n";
import type { Review } from "@/services/review/types";
import { REVIEW_CATEGORIES } from "@/services/review/types";
import styles from "./ReviewSummary.module.css";

interface ReviewSummaryProps {
	reviews: Review[];
}

export function ReviewSummary({ reviews }: ReviewSummaryProps) {
	const { t } = useTranslation();

	if (reviews.length === 0) {
		return (
			<EmptyState
				title={t("workspace.review.emptyTitle")}
				description={t("workspace.review.emptyDescription")}
			/>
		);
	}

	return (
		<div className={styles.summary}>
			{reviews.map((review) => (
				<article className={styles.reviewCard} key={review.id}>
					<p className={styles.reviewMeta}>
						{t("workspace.review.historyVersion", {
							version: review.executionVersionNumber,
						})}{" "}
						· {new Date(review.createdAt).toLocaleString()}
					</p>
					<p className={styles.reviewMeta}>
						{REVIEW_CATEGORIES.map(
							(category) =>
								`${t(`workspace.review.categoryLabels.${category}`)} ${review.scores[category]}/5`,
						).join(" · ")}
					</p>
					{review.comment ? (
						<p className={styles.reviewComment}>{review.comment}</p>
					) : null}
				</article>
			))}
		</div>
	);
}
