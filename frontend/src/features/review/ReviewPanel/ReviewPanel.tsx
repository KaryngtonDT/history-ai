import { useState } from "react";
import { useTranslation } from "@/i18n";
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
	const { t } = useTranslation();
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
				setMessage(t("workspace.review.feedbackSaved"));
				onSaved?.();
			})
			.catch(() => {
				setMessage(t("workspace.review.feedbackSaveFailed"));
			})
			.finally(() => {
				setSaving(false);
			});
	};

	return (
		<section className={styles.reviewPanel}>
			<div className={styles.header}>
				<h2 className={styles.title}>{t("workspace.review.title")}</h2>
			</div>

			<div className={styles.categories}>
				{REVIEW_CATEGORIES.map((category) => (
					<div className={styles.categoryRow} key={category}>
						<span className={styles.categoryLabel}>
							{t(`workspace.review.categoryLabels.${category}`)}
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
									aria-label={t("workspace.review.starsAria", {
										category: t(`workspace.review.categoryLabels.${category}`),
										score,
									})}
								>
									★
								</button>
							))}
						</div>
					</div>
				))}
			</div>

			<label htmlFor={`review-comment-${videoId}`}>
				{t("workspace.review.comment")}
			</label>
			<textarea
				id={`review-comment-${videoId}`}
				className={styles.commentField}
				value={comment}
				onChange={(event) => {
					setComment(event.target.value);
				}}
				placeholder={t("workspace.review.commentPlaceholder")}
			/>

			<div className={styles.actions}>
				<button
					type="button"
					className={styles.saveButton}
					onClick={handleSave}
					disabled={saving}
				>
					{saving
						? t("workspace.review.saving")
						: t("workspace.review.saveFeedback")}
				</button>
			</div>

			{message ? <p className={styles.message}>{message}</p> : null}
		</section>
	);
}
