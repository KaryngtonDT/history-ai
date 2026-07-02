import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowMentorService } from "@/services/shadowMentor/ShadowMentorService";
import type {
	GoalImpact,
	LearningGoal,
	MentorDashboard,
	MentorMission,
	RoadmapStep,
	SkillProgress,
	WeeklyReview,
} from "@/services/shadowMentor/types";
import styles from "../shadowMentor.module.css";

function ProgressBar({ value }: { value: number }) {
	return (
		<div className={styles.progressBar} aria-hidden="true">
			<span style={{ width: `${Math.min(100, value)}%` }} />
		</div>
	);
}

function CurrentGoalPanel({ goal }: { goal: LearningGoal | null }) {
	const { t } = useTranslation();

	if (!goal) {
		return <p>{t("shadowMentor.empty.goal")}</p>;
	}

	return (
		<article className={styles.card}>
			<strong>{goal.title}</strong>
			<p>{goal.description}</p>
			<p className={styles.meta}>{goal.motivation}</p>
			<p className={styles.meta}>
				{t("shadowMentor.goal.category", { value: goal.category })} ·{" "}
				{t("shadowMentor.goal.priority", { value: goal.priority })} ·{" "}
				{t("shadowMentor.statusLabel", { value: goal.status })}
			</p>
			<p className={styles.meta}>
				{t("shadowMentor.goal.progress", {
					percent: String(goal.progressPercent),
				})}
			</p>
			<ProgressBar value={goal.progressPercent} />
			{goal.deadline ? (
				<p className={styles.meta}>
					{t("shadowMentor.goal.deadline", {
						date: new Date(goal.deadline).toLocaleDateString(),
					})}
				</p>
			) : null}
			<div className={styles.tagList}>
				{goal.targetSkills.map((skill) => (
					<span key={skill} className={styles.tag}>
						{skill}
					</span>
				))}
			</div>
		</article>
	);
}

function RoadmapPanel({ steps }: { steps: RoadmapStep[] }) {
	const { t } = useTranslation();

	if (steps.length === 0) {
		return <p>{t("shadowMentor.empty.roadmap")}</p>;
	}

	return (
		<div className={styles.roadmapSteps}>
			{steps.map((step) => (
				<article
					key={`${step.horizon}-${step.order}`}
					className={styles.roadmapStep}
				>
					<span className={styles.horizon}>
						{t(`shadowMentor.horizons.${step.horizon}`)}
					</span>
					<strong>{step.label}</strong>
					<p className={styles.meta}>{step.detail}</p>
				</article>
			))}
		</div>
	);
}

function MissionPanel({ mission }: { mission: MentorMission | null }) {
	const { t } = useTranslation();

	if (!mission) {
		return <p>{t("shadowMentor.empty.mission")}</p>;
	}

	return (
		<article className={styles.card}>
			<strong>{mission.title}</strong>
			<p>{mission.objective}</p>
			<p className={styles.meta}>
				{t("shadowMentor.mission.duration", {
					minutes: String(mission.durationMinutes),
				})}{" "}
				· {t("shadowMentor.statusLabel", { value: mission.status })}
			</p>
			<p className={styles.meta}>
				{t("shadowMentor.mission.exercises", {
					count: String(mission.exerciseCount),
				})}
			</p>
			<p className={styles.meta}>{mission.validationLabel}</p>
			<ProgressBar value={mission.progressPercent} />
		</article>
	);
}

function SkillsPanel({ skills }: { skills: SkillProgress[] }) {
	const { t } = useTranslation();

	if (skills.length === 0) {
		return <p>{t("shadowMentor.empty.skills")}</p>;
	}

	return (
		<ul className={styles.list}>
			{skills.map((skill) => (
				<li key={skill.key} className={styles.listItem}>
					<div className={styles.skillRow}>
						<strong>{skill.label}</strong>
						<p className={styles.meta}>
							{t("shadowMentor.skills.percent", {
								percent: String(skill.percent),
							})}
						</p>
						<ProgressBar value={skill.percent} />
					</div>
				</li>
			))}
		</ul>
	);
}

function WeeklyReviewPanel({ review }: { review: WeeklyReview }) {
	const { t } = useTranslation();

	if (!review.summary) {
		return <p>{t("shadowMentor.empty.weeklyReview")}</p>;
	}

	return (
		<article className={styles.card}>
			<strong>{t("shadowMentor.weeklyReview.title")}</strong>
			<p>{review.summary}</p>
			<p className={styles.meta}>
				{t("shadowMentor.weeklyReview.progressDelta", {
					delta: String(review.progressDelta),
				})}
			</p>
			<p className={styles.meta}>
				{t("shadowMentor.weeklyReview.milestonesCompleted", {
					count: String(review.milestonesCompleted),
				})}
			</p>
			<p className={styles.meta}>{review.difficultyNote}</p>
			{review.recommendations.length > 0 ? (
				<ul className={styles.list}>
					{review.recommendations.map((item) => (
						<li key={item} className={styles.listItem}>
							{item}
						</li>
					))}
				</ul>
			) : null}
			{review.adaptationPending ? (
				<p className={styles.adaptationPending}>
					{t("shadowMentor.weeklyReview.adaptationPending")}
				</p>
			) : null}
			{review.generatedAt ? (
				<p className={styles.meta}>
					{new Date(review.generatedAt).toLocaleString()}
				</p>
			) : null}
		</article>
	);
}

function GoalImpactPanel({ impacts }: { impacts: GoalImpact[] }) {
	const { t } = useTranslation();

	if (impacts.length === 0) {
		return <p>{t("shadowMentor.empty.goalImpact")}</p>;
	}

	return (
		<ul className={styles.list}>
			{impacts.map((impact) => (
				<li key={impact.goalId} className={styles.listItem}>
					<strong>{impact.goalTitle}</strong>
					<p className={styles.meta}>
						{t("shadowMentor.goalImpact.percent", {
							percent: String(impact.impactPercent),
						})}
					</p>
					<p>{impact.reason}</p>
					<ProgressBar value={impact.impactPercent} />
				</li>
			))}
		</ul>
	);
}

export function MentorCenter() {
	const { t } = useTranslation();
	const [dashboard, setDashboard] = useState<MentorDashboard | null>(null);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const next = await shadowMentorService.getDashboard();
		setDashboard(next);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowMentor.errors.loadFailed"));
		});
	}, [load, t]);

	const handleReset = async () => {
		setError(null);
		setMessage(null);

		try {
			await shadowMentorService.resetGoals();
			await load();
			setMessage(t("shadowMentor.reset.success"));
		} catch {
			setError(t("shadowMentor.errors.resetFailed"));
		}
	};

	if (!dashboard) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowMentor}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.section}>
				<h2>{t("shadowMentor.currentGoal.title")}</h2>
				<CurrentGoalPanel goal={dashboard.primaryGoal} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.roadmap.title")}</h2>
				<RoadmapPanel steps={dashboard.plan.roadmap} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.currentMission.title")}</h2>
				<MissionPanel mission={dashboard.currentMission} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.nextMilestone.title")}</h2>
				{dashboard.nextMilestone ? (
					<article className={styles.card}>
						<strong>{dashboard.nextMilestone.label}</strong>
						<p>{dashboard.nextMilestone.detail}</p>
						{dashboard.nextMilestone.targetAt ? (
							<p className={styles.meta}>
								{t("shadowMentor.milestone.target", {
									date: new Date(
										dashboard.nextMilestone.targetAt,
									).toLocaleDateString(),
								})}
							</p>
						) : null}
					</article>
				) : (
					<p>{t("shadowMentor.empty.milestone")}</p>
				)}
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.eta.title")}</h2>
				<article className={styles.card}>
					<strong>
						{dashboard.plan.estimatedCompletionAt
							? new Date(
									dashboard.plan.estimatedCompletionAt,
								).toLocaleDateString()
							: t("shadowMentor.eta.unknown")}
					</strong>
					<p className={styles.meta}>{t("shadowMentor.eta.description")}</p>
				</article>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.skills.title")}</h2>
				<SkillsPanel skills={dashboard.plan.skills} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.weeklyReview.title")}</h2>
				<WeeklyReviewPanel review={dashboard.plan.weeklyReview} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.goalImpact.title")}</h2>
				<GoalImpactPanel impacts={dashboard.goalImpact} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMentor.actions.title")}</h2>
				<div className={styles.actions}>
					<button type="button" onClick={() => void handleReset()}>
						{t("shadowMentor.reset.action")}
					</button>
				</div>
			</section>
		</div>
	);
}
