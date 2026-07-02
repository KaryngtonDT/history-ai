import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowRelationshipService } from "@/services/shadowRelationship/ShadowRelationshipService";
import type {
	RelationshipPortrait,
	RelationshipProfile,
} from "@/services/shadowRelationship/types";
import styles from "./shadowRelationship.module.css";

function strengthLevel(strength: string): number {
	switch (strength) {
		case "very_high":
			return 4;
		case "high":
			return 3;
		case "medium":
			return 2;
		default:
			return 1;
	}
}

function StrengthMeter({ strength }: { strength: string }) {
	const level = strengthLevel(strength);

	return (
		<span className={styles.meter} aria-hidden="true">
			{[1, 2, 3, 4].map((value) => (
				<span key={value} data-active={value <= level} />
			))}
		</span>
	);
}

export function ShadowRelationshipCenter() {
	const { t } = useTranslation();
	const [profile, setProfile] = useState<RelationshipProfile | null>(null);
	const [portrait, setPortrait] = useState<RelationshipPortrait | null>(null);
	const [utterance, setUtterance] = useState("");
	const [pendingConfirmation, setPendingConfirmation] = useState(false);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [nextProfile, nextPortrait] = await Promise.all([
			shadowRelationshipService.getProfile(),
			shadowRelationshipService.getPortrait(),
		]);
		setProfile(nextProfile);
		setPortrait(nextPortrait);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowRelationship.errors.loadFailed"));
		});
	}, [load, t]);

	const handleConfigure = async (confirmed = false) => {
		setError(null);
		setMessage(null);

		try {
			const result = await shadowRelationshipService.configure(
				utterance,
				confirmed,
			);
			setProfile(result.profile);
			setPortrait(result.portrait);
			setPendingConfirmation(result.requiresConfirmation);
			setMessage(result.confirmationMessage);

			if (result.applied) {
				setUtterance("");
				setPendingConfirmation(false);
			}
		} catch {
			setError(t("shadowRelationship.errors.updateFailed"));
		}
	};

	const handleReset = async () => {
		setError(null);
		try {
			const next = await shadowRelationshipService.reset();
			setProfile(next);
			await load();
			setMessage(t("shadowRelationship.reset.success"));
		} catch {
			setError(t("shadowRelationship.errors.resetFailed"));
		}
	};

	const handleApprove = async (changeId: string) => {
		const result = await shadowRelationshipService.approveChange(changeId);
		setProfile(result.profile);
		setPortrait(result.portrait);
	};

	if (!profile || !portrait) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowRelationship}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.scoreCard}>
				<h2>{t("shadowRelationship.portrait.title")}</h2>
				<p>{t("shadowRelationship.portrait.description")}</p>
				<div className={styles.scoreValue}>
					{t("shadowRelationship.portrait.score", {
						score: portrait.relationshipScore,
					})}
				</div>
			</section>

			<section className={styles.section}>
				<h3>{t("shadowRelationship.portrait.confirmed")}</h3>
				<ul className={styles.traitList}>
					{portrait.confirmed.map((trait) => (
						<li key={`${trait.type}-${trait.key}`} className={styles.traitItem}>
							<strong>✓ {trait.label}</strong>
							<p>{trait.explanation}</p>
							<StrengthMeter strength={trait.strength} />
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h3>{t("shadowRelationship.portrait.hypotheses")}</h3>
				<ul className={styles.traitList}>
					{portrait.hypotheses.map((trait) => (
						<li key={`${trait.type}-${trait.key}`} className={styles.traitItem}>
							<strong>? {trait.label}</strong>
							<p>{trait.explanation}</p>
							<StrengthMeter strength={trait.strength} />
						</li>
					))}
				</ul>
			</section>

			<section className={styles.section}>
				<h3>{t("shadowRelationship.interests.title")}</h3>
				<ul className={styles.traitList}>
					{profile.traits
						.filter((trait) => trait.type === "interest")
						.map((trait) => (
							<li key={trait.key} className={styles.traitItem}>
								<strong>{trait.label}</strong>
								<StrengthMeter strength={trait.strength} />
							</li>
						))}
				</ul>
			</section>

			<section className={styles.section}>
				<h3>{t("shadowRelationship.habits.title")}</h3>
				<ul className={styles.traitList}>
					{profile.traits
						.filter((trait) => trait.type === "habit")
						.map((trait) => (
							<li key={trait.key} className={styles.traitItem}>
								{trait.label}
							</li>
						))}
				</ul>
			</section>

			<section className={styles.section}>
				<h3>{t("shadowRelationship.timeline.title")}</h3>
				<ul className={styles.timelineList}>
					{profile.timeline.map((entry) => (
						<li key={entry.id} className={styles.timelineItem}>
							<strong>{entry.label}</strong>
							<p>{entry.detail}</p>
							<small>{new Date(entry.recordedAt).toLocaleDateString()}</small>
						</li>
					))}
				</ul>
			</section>

			{portrait.pendingChanges.length > 0 ? (
				<section className={styles.section}>
					<h3>{t("shadowRelationship.pending.title")}</h3>
					{portrait.pendingChanges.map((change) => (
						<div key={change.id} className={styles.pendingCard}>
							<p>{change.label}</p>
							<div className={styles.actions}>
								<button
									type="button"
									onClick={() => void handleApprove(change.id)}
								>
									{t("common.yes")}
								</button>
								<button
									type="button"
									onClick={() =>
										void shadowRelationshipService
											.rejectChange(change.id)
											.then(load)
									}
								>
									{t("common.no")}
								</button>
							</div>
						</div>
					))}
				</section>
			) : null}

			<section className={styles.section}>
				<h3>{t("shadowRelationship.questions.title")}</h3>
				<ul className={styles.questionList}>
					{portrait.questions.map((question) => (
						<li key={question.id} className={styles.questionItem}>
							{question.text}
						</li>
					))}
				</ul>
			</section>

			<section className={styles.teachBox}>
				<h3>{t("shadowRelationship.teach.title")}</h3>
				<p>{t("shadowRelationship.teach.description")}</p>
				<textarea
					value={utterance}
					onChange={(event) => setUtterance(event.target.value)}
					placeholder={t("shadowRelationship.teach.placeholder")}
				/>
				<div className={styles.actions}>
					<button type="button" onClick={() => void handleConfigure(false)}>
						{t("shadowRelationship.teach.preview")}
					</button>
					{pendingConfirmation ? (
						<button type="button" onClick={() => void handleConfigure(true)}>
							{t("shadowRelationship.teach.apply")}
						</button>
					) : null}
					<button type="button" onClick={() => void handleReset()}>
						{t("shadowRelationship.reset.action")}
					</button>
				</div>
			</section>
		</div>
	);
}
