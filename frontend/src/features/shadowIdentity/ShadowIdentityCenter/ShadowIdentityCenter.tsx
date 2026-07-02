import { useCallback, useState } from "react";
import { VoiceStudio } from "@/features/shadow/VoiceStudio";
import { useTranslation } from "@/i18n";
import { shadowIdentityService } from "@/services/shadowIdentity/ShadowIdentityService";
import type { ShadowIdentityProfile } from "@/services/shadowIdentity/types";
import styles from "../shadowIdentity.module.css";

const PERSONAS = [
	"teacher",
	"coach",
	"storyteller",
	"professor",
	"friendly_companion",
	"debater",
	"socratic_mentor",
	"documentary_narrator",
	"technical_expert",
] as const;

interface ShadowIdentityCenterProps {
	profile: ShadowIdentityProfile;
	onProfileChange: (profile: ShadowIdentityProfile) => void;
	isUpdating: boolean;
}

export function ShadowIdentityCenter({
	profile,
	onProfileChange,
	isUpdating,
}: ShadowIdentityCenterProps) {
	const { t } = useTranslation();
	const [utterance, setUtterance] = useState("");
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [pendingConfirmation, setPendingConfirmation] = useState(false);

	const handlePersonaChange = async (persona: string) => {
		setError(null);
		try {
			const updated = await shadowIdentityService.updatePreferences({
				persona,
			});
			onProfileChange(updated);
		} catch {
			setError(t("shadowIdentity.errors.updateFailed"));
		}
	};

	const handleReset = async () => {
		setError(null);
		try {
			const updated = await shadowIdentityService.reset();
			onProfileChange(updated);
			setMessage(t("shadowIdentity.reset.success"));
		} catch {
			setError(t("shadowIdentity.errors.resetFailed"));
		}
	};

	const handleConfigure = useCallback(
		async (confirmed = false) => {
			if (!utterance.trim()) {
				return;
			}

			setError(null);
			try {
				const result = await shadowIdentityService.configure(
					utterance,
					confirmed,
				);
				setMessage(result.confirmationMessage);
				setPendingConfirmation(result.requiresConfirmation);
				if (result.applied) {
					onProfileChange(result.profile);
					setUtterance("");
					setPendingConfirmation(false);
				}
			} catch {
				setError(t("shadowIdentity.errors.configureFailed"));
			}
		},
		[onProfileChange, t, utterance],
	);

	return (
		<div className={styles.root}>
			<section className={styles.card}>
				<h2 className={styles.title}>
					{t("shadowIdentity.voiceStudio.title")}
				</h2>
				<VoiceStudio />
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.persona.title")}</h2>
				<p className={styles.description}>
					{t("shadowIdentity.persona.description")}
				</p>
				<select
					className={styles.select}
					value={profile.preferences.persona}
					disabled={isUpdating}
					onChange={(event) => void handlePersonaChange(event.target.value)}
				>
					{PERSONAS.map((persona) => (
						<option key={persona} value={persona}>
							{t(`shadowIdentity.persona.options.${persona}`)}
						</option>
					))}
				</select>
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>
					{t("shadowIdentity.conversation.title")}
				</h2>
				<div className={styles.grid}>
					<div>
						<strong>{t("shadowIdentity.conversation.challenge")}</strong>
						<p>{profile.preferences.challengeLevel}/5</p>
					</div>
					<div>
						<strong>{t("shadowIdentity.conversation.humor")}</strong>
						<p>{profile.preferences.humorLevel}</p>
					</div>
					<div>
						<strong>{t("shadowIdentity.conversation.examples")}</strong>
						<p>{profile.preferences.examplesLevel}/10</p>
					</div>
				</div>
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.language.title")}</h2>
				<div className={styles.grid}>
					<div>
						<strong>{t("shadowIdentity.language.primary")}</strong>
						<p>{profile.preferences.languageProfile.primaryLanguage}</p>
					</div>
					<div>
						<strong>{t("shadowIdentity.language.technicalTerms")}</strong>
						<p>{profile.preferences.languageProfile.technicalTermsPolicy}</p>
					</div>
					<div>
						<strong>{t("shadowIdentity.language.pronunciation")}</strong>
						<p>{profile.preferences.languageProfile.pronunciation}</p>
					</div>
				</div>
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.memory.title")}</h2>
				<div className={styles.grid}>
					<div>
						<strong>{t("shadowIdentity.memory.interests")}</strong>
						<p>
							{profile.preferences.memoryPolicy.interests.join(", ") || "—"}
						</p>
					</div>
					<div>
						<strong>{t("shadowIdentity.memory.goals")}</strong>
						<p>{profile.preferences.memoryPolicy.goals.join(", ") || "—"}</p>
					</div>
				</div>
				<div className={styles.actions}>
					<button
						type="button"
						className={styles.button}
						disabled={isUpdating}
						onClick={() => void handleReset()}
					>
						{t("shadowIdentity.reset.action")}
					</button>
				</div>
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.dna.title")}</h2>
				{Object.entries(profile.dna).map(([key, value]) => (
					<div key={key} className={styles.dnaRow}>
						<div className={styles.dnaLabel}>
							<span>{t(`shadowIdentity.dna.${key}`)}</span>
							<span>{value}/10</span>
						</div>
						<div
							className={styles.dnaBar}
							style={{ width: `${Math.min(100, value * 10)}%` }}
						/>
					</div>
				))}
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.history.title")}</h2>
				<ul className={styles.historyList}>
					{profile.history.map((entry) => (
						<li key={entry.id} className={styles.historyItem}>
							{entry.label} · {entry.source}
						</li>
					))}
				</ul>
			</section>

			<section className={styles.card}>
				<h2 className={styles.title}>{t("shadowIdentity.teach.title")}</h2>
				<p className={styles.description}>
					{t("shadowIdentity.teach.description")}
				</p>
				<div className={styles.teachForm}>
					<textarea
						className={styles.textarea}
						value={utterance}
						rows={3}
						placeholder={t("shadowIdentity.teach.placeholder")}
						onChange={(event) => setUtterance(event.target.value)}
					/>
					<div className={styles.actions}>
						<button
							type="button"
							className={styles.buttonPrimary}
							disabled={isUpdating}
							onClick={() => void handleConfigure(pendingConfirmation)}
						>
							{pendingConfirmation
								? t("shadowIdentity.teach.confirm")
								: t("shadowIdentity.teach.action")}
						</button>
					</div>
					{message ? <p className={styles.message}>{message}</p> : null}
					{error ? <p className={styles.error}>{error}</p> : null}
				</div>
			</section>
		</div>
	);
}
