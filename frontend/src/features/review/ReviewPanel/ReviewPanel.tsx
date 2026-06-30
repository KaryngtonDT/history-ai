import { useState } from "react";
import { reviewService } from "@/services/review/ReviewService";
import type { ReviewCategory, ReviewScores } from "@/services/review/types";
import { REVIEW_CATEGORIES } from "@/services/review/types";
import styles from "./ReviewPanel.module.css";

interface ReviewPanelProps {
	videoId: string | null;
	executionVersionNumber?: number;
	onSaved?: () => void;
}

export function ReviewPanel({
	videoId,
	executionVersionNumber = 1,
	onSaved,
}: ReviewPanelProps) {
	const [scores, setScores] = useState<ReviewScores>(
		reviewService.defaultScores(),
	);
	const [comment, setComment] = useState("");
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState<string | null>(null);

	if (!videoId) {
		return null;
	}

	const handleScoreChange = (category: ReviewCategory, score: number): void => {
		setScores((current) => ({
			...current,
			[category]: score,
		}));
	};

	const handleSave = (): void => {
		setSaving(true);
		setMessage(null);

		void reviewService
			.saveReview(videoId, {
				executionVersionNumber,
				scores,
				comment,
			})
			.then(() => {
				setMessage("Feedback saved.");
				onSaved?.();
			})
			.catch(() => {
				setMessage("Could not save feedback.");
			})
			.finally(() => {
				setSaving(false);
			});
	};

	return (
		<section className={styles.reviewPanel}>
			<div className={styles.header}>
				<h2 className={styles.title}>Review</h2>
			</div>

			<div className={styles.categories}>
				{REVIEW_CATEGORIES.map((category) => (
					<div className={styles.categoryRow} key={category}>
						<span className={styles.categoryLabel}>
							{reviewService.formatCategory(category)}
						</span>
						<div className={styles.stars}>
							{[1, 2, 3, 4, 5].map((score) => (
								<button
									type="button"
									key={score}
									className={`${styles.starButton} ${
										scores[category] >= score ? styles.starButtonActive : ""
									}`}
									onClick={() => {
										handleScoreChange(category, score);
									}}
									aria-label={`${reviewService.formatCategory(category)} ${score} stars`}
								>
									★
								</button>
							))}
						</div>
					</div>
				))}
			</div>

			<label htmlFor={`review-comment-${videoId}`}>Comment</label>
			<textarea
				id={`review-comment-${videoId}`}
				className={styles.commentField}
				value={comment}
				onChange={(event) => {
					setComment(event.target.value);
				}}
				placeholder="The cloned voice is slightly too robotic."
			/>

			<div className={styles.actions}>
				<button
					type="button"
					className={styles.saveButton}
					onClick={handleSave}
					disabled={saving}
				>
					{saving ? "Saving..." : "Save Feedback"}
				</button>
			</div>

			{message ? <p className={styles.message}>{message}</p> : null}
		</section>
	);
}
