import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowTeachingService } from "@/services/shadowTeaching/ShadowTeachingService";
import type {
	TeachingCurrentResponse,
	TeachingPlan,
	TeachingVoiceMode,
} from "@/services/shadowTeaching/types";
import styles from "../shadowTeaching.module.css";

function ProgressBar({ value }: { value: number }) {
	return (
		<div className={styles.progressBar} aria-hidden="true">
			<span style={{ width: `${Math.min(100, value)}%` }} />
		</div>
	);
}

export function ShadowTeachingCenter() {
	const { t } = useTranslation();
	const [plan, setPlan] = useState<TeachingPlan | null>(null);
	const [current, setCurrent] = useState<TeachingCurrentResponse | null>(null);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [nextPlan, nextCurrent] = await Promise.all([
			shadowTeachingService.getPath(),
			shadowTeachingService.getCurrent(),
		]);
		setPlan(nextPlan);
		setCurrent(nextCurrent);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowTeaching.errors.loadFailed"));
		});
	}, [load, t]);

	const handleVoiceMode = async (voiceMode: TeachingVoiceMode) => {
		setError(null);
		setMessage(null);

		try {
			const preferences = await shadowTeachingService.updatePreferences({
				voiceMode,
			});
			setPlan((previous) =>
				previous ? { ...previous, preferences } : previous,
			);
			setMessage(t("shadowTeaching.preferences.saved"));
		} catch {
			setError(t("shadowTeaching.errors.preferencesFailed"));
		}
	};

	const handleReset = async () => {
		setError(null);
		setMessage(null);

		try {
			const nextPlan = await shadowTeachingService.reset();
			setPlan(nextPlan);
			const nextCurrent = await shadowTeachingService.getCurrent();
			setCurrent(nextCurrent);
			setMessage(t("shadowTeaching.reset.success"));
		} catch {
			setError(t("shadowTeaching.errors.resetFailed"));
		}
	};

	if (!plan) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowTeaching}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.section}>
				<h2>{t("shadowTeaching.learningPath.title")}</h2>
				<div className={styles.cardGrid}>
					{plan.learningPath.map((mission) => (
						<article key={mission.id} className={styles.card}>
							<strong>{mission.label}</strong>
							<p>{mission.detail}</p>
							<p className={styles.meta}>
								{t("shadowTeaching.statusLabel", { value: mission.status })}
							</p>
							<ProgressBar value={mission.progressPercent} />
						</article>
					))}
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.currentLesson.title")}</h2>
				{current?.lesson ? (
					<article className={styles.card}>
						<strong>{current.lesson.title}</strong>
						<p>{current.lesson.summary}</p>
						<p className={styles.meta}>
							{t("shadowTeaching.currentLesson.exercisesDue", {
								count: String(current.exercisesDue),
							})}
						</p>
						<p className={styles.meta}>
							{t("shadowTeaching.currentLesson.revisionDue", {
								count: String(current.revisionDue),
							})}
						</p>
					</article>
				) : (
					<p>{t("shadowTeaching.empty.currentLesson")}</p>
				)}
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.objectives.title")}</h2>
				<ul className={styles.list}>
					{plan.objectives.map((objective) => (
						<li key={objective.id} className={styles.listItem}>
							<strong>{objective.label}</strong>
							<p>{objective.detail}</p>
							<p className={styles.meta}>
								{t("shadowTeaching.statusLabel", { value: objective.status })}
							</p>
							<ProgressBar value={objective.progressPercent} />
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.exercises.title")}</h2>
				<ul className={styles.list}>
					{plan.exercises.map((exercise) => (
						<li key={exercise.id} className={styles.listItem}>
							<strong>{exercise.title}</strong>
							<p>{exercise.prompt}</p>
							<p className={styles.meta}>
								{exercise.difficulty} ·{" "}
								{t("shadowTeaching.statusLabel", { value: exercise.status })}
							</p>
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.revisionQueue.title")}</h2>
				<ul className={styles.list}>
					{plan.revisions.map((revision) => (
						<li key={revision.id} className={styles.listItem}>
							<strong>{revision.label}</strong>
							<p>{revision.reason}</p>
							<p className={styles.meta}>
								{new Date(revision.dueAt).toLocaleString()}
							</p>
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.progress.title")}</h2>
				<div className={styles.cardGrid}>
					<article className={styles.card}>
						<strong>{t("shadowTeaching.progress.objectives")}</strong>
						<p>
							{plan.progress.completedObjectives}/
							{plan.progress.totalObjectives}
						</p>
					</article>
					<article className={styles.card}>
						<strong>{t("shadowTeaching.progress.exercises")}</strong>
						<p>
							{plan.progress.completedExercises}/{plan.progress.totalExercises}
						</p>
					</article>
					<article className={styles.card}>
						<strong>{t("shadowTeaching.progress.checkpoints")}</strong>
						<p>
							{plan.progress.completedCheckpoints}/
							{plan.progress.totalCheckpoints}
						</p>
					</article>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.history.title")}</h2>
				<ul className={styles.list}>
					{plan.history.map((entry) => (
						<li key={entry.id} className={styles.listItem}>
							<strong>{entry.label}</strong>
							<p>{entry.detail}</p>
							<p className={styles.meta}>
								{new Date(entry.recordedAt).toLocaleString()}
							</p>
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowTeaching.preferences.title")}</h2>
				<div className={styles.preferences}>
					<p>{t("shadowTeaching.preferences.voiceMode")}</p>
					<div className={styles.voiceModes}>
						{(["coach", "mentor", "story"] as const).map((mode) => (
							<label key={mode} className={styles.voiceMode}>
								<input
									type="radio"
									name="shadowTeachingVoiceMode"
									checked={plan.preferences.voiceMode === mode}
									onChange={() => void handleVoiceMode(mode)}
								/>
								<span>{t(`shadowTeaching.preferences.modes.${mode}`)}</span>
							</label>
						))}
					</div>
					<div className={styles.actions}>
						<button type="button" onClick={() => void handleReset()}>
							{t("shadowTeaching.reset.action")}
						</button>
					</div>
				</div>
			</section>
		</div>
	);
}
