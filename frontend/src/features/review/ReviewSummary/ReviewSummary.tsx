import type { Review } from "@/services/review/types";
import { REVIEW_CATEGORIES } from "@/services/review/types";
import styles from "./ReviewSummary.module.css";

interface ReviewSummaryProps {
	reviews: Review[];
}

export function ReviewSummary({ reviews }: ReviewSummaryProps) {
	if (reviews.length === 0) {
		return <p className={styles.empty}>No reviews yet.</p>;
	}

	return (
		<div className={styles.summary}>
			{reviews.map((review) => (
				<article className={styles.reviewCard} key={review.id}>
					<p className={styles.reviewMeta}>
						Version {review.executionVersionNumber} ·{" "}
						{new Date(review.createdAt).toLocaleString()}
					</p>
					<p className={styles.reviewMeta}>
						{REVIEW_CATEGORIES.map(
							(category) =>
								`${category.replace("_", " ")} ${review.scores[category]}/5`,
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
