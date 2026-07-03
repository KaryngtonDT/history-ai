import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router";
import { useTranslation } from "@/i18n";
import { presenceService } from "@/services/presence/PresenceService";
import type {
	PresenceContext,
	PresenceExplain,
	PresenceHistory,
	PresencePreferences,
	PresenceSessionResponse,
	PresenceSurface,
} from "@/services/presence/types";
import styles from "../presence.module.css";

const FUTURE_SURFACES: PresenceSurface[] = ["browser", "ide", "mobile"];

export function PresenceCenter() {
	const { t } = useTranslation();
	const [session, setSession] = useState<PresenceSessionResponse | null>(null);
	const [preferences, setPreferences] = useState<PresencePreferences | null>(
		null,
	);
	const [context, setContext] = useState<PresenceContext | null>(null);
	const [history, setHistory] = useState<PresenceHistory | null>(null);
	const [explain, setExplain] = useState<PresenceExplain | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [saving, setSaving] = useState(false);

	const load = useCallback(async () => {
		setError(null);
		const [workspace, contextResponse, historyResponse, explainResponse] =
			await Promise.all([
				presenceService.connect({ surface: "web" }),
				presenceService.getContext("web"),
				presenceService.getHistory(20),
				presenceService.getExplain(),
			]);
		setSession(workspace.session);
		setPreferences(workspace.preferences);
		setContext(contextResponse);
		setHistory(historyResponse);
		setExplain(explainResponse);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("presence.errors.loadFailed"));
		});
	}, [load, t]);

	async function handleConnect() {
		const workspace = await presenceService.connect({ surface: "web" });
		setSession(workspace.session);
		setPreferences(workspace.preferences);
	}

	async function handleDisconnect() {
		const workspace = await presenceService.disconnect();
		setSession(workspace.session);
	}

	async function handleSavePreferences() {
		if (!preferences) {
			return;
		}

		setSaving(true);
		try {
			const response = await presenceService.updatePreferences({
				shortcuts: preferences.shortcuts,
				notifications: preferences.notifications,
				voiceEnabled: preferences.voiceEnabled,
				proactiveEnabled: preferences.proactiveEnabled,
				surfaceEnabled: preferences.surfaceEnabled,
			});
			setPreferences(response.preferences);
		} finally {
			setSaving(false);
		}
	}

	function togglePreference(key: keyof PresencePreferences, value: boolean) {
		if (!preferences) {
			return;
		}

		setPreferences({ ...preferences, [key]: value });
	}

	function toggleSurface(surface: PresenceSurface, enabled: boolean) {
		if (!preferences) {
			return;
		}

		setPreferences({
			...preferences,
			surfaceEnabled: {
				...preferences.surfaceEnabled,
				[surface]: enabled,
			},
		});
	}

	if (error) {
		return <p role="alert">{error}</p>;
	}

	if (!session || !preferences) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.presence}>
			<section className={styles.section}>
				<h2>{t("presence.status.title")}</h2>
				<div className={styles.card}>
					<p>
						{session.active && session.session
							? t("presence.status.active", {
									surface: session.session.surface,
								})
							: t("presence.status.inactive")}
					</p>
					{session.session ? (
						<p className={styles.meta}>
							{t("presence.status.lastActive", {
								time: new Date(session.session.lastActiveAt).toLocaleString(),
							})}
						</p>
					) : null}
					<div className={styles.actions}>
						<button type="button" onClick={() => void handleConnect()}>
							{t("presence.status.connect")}
						</button>
						<button type="button" onClick={() => void handleDisconnect()}>
							{t("presence.status.disconnect")}
						</button>
					</div>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("presence.surfaces.title")}</h2>
				<div className={styles.cardGrid}>
					{(["web", "desktop", ...FUTURE_SURFACES] as PresenceSurface[]).map(
						(surface) => {
							const isFuture = FUTURE_SURFACES.includes(surface);

							return (
								<div
									key={surface}
									className={
										isFuture
											? `${styles.card} ${styles.surfaceDisabled}`
											: styles.card
									}
								>
									<strong>{t(`presence.surfaces.${surface}`)}</strong>
									{isFuture ? (
										<span className={styles.badgeSoon}>
											{t("presence.surfaces.comingSoon")}
										</span>
									) : (
										<label className={styles.checkboxLabel}>
											<input
												type="checkbox"
												checked={preferences.surfaceEnabled[surface]}
												onChange={(event) =>
													toggleSurface(surface, event.target.checked)
												}
											/>
											{t("common.yes")}
										</label>
									)}
								</div>
							);
						},
					)}
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("presence.preferences.title")}</h2>
				<div className={styles.card}>
					<div className={styles.fieldRow}>
						<span>{t("presence.preferences.shortcut")}</span>
						<input
							className={styles.input}
							value={preferences.shortcuts.quickAssist ?? ""}
							onChange={(event) =>
								setPreferences({
									...preferences,
									shortcuts: {
										...preferences.shortcuts,
										quickAssist: event.target.value,
									},
								})
							}
						/>
					</div>
					<label className={styles.checkboxLabel}>
						<input
							type="checkbox"
							checked={preferences.notifications}
							onChange={(event) =>
								togglePreference("notifications", event.target.checked)
							}
						/>
						{t("presence.preferences.notifications")}
					</label>
					<label className={styles.checkboxLabel}>
						<input
							type="checkbox"
							checked={preferences.voiceEnabled}
							onChange={(event) =>
								togglePreference("voiceEnabled", event.target.checked)
							}
						/>
						{t("presence.preferences.voice")}
					</label>
					<label className={styles.checkboxLabel}>
						<input
							type="checkbox"
							checked={preferences.proactiveEnabled}
							onChange={(event) =>
								togglePreference("proactiveEnabled", event.target.checked)
							}
						/>
						{t("presence.preferences.proactive")}
					</label>
					<button
						type="button"
						disabled={saving}
						onClick={() => void handleSavePreferences()}
					>
						{t("presence.preferences.save")}
					</button>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("presence.permissions.title")}</h2>
				<ul className={styles.list}>
					{preferences.permissions.map((permission) => (
						<li key={permission.capability} className={styles.listItem}>
							<strong>{permission.capability.replace(/_/g, " ")}</strong>
							<span className={styles.meta}>
								{permission.granted
									? t("presence.permissions.granted")
									: t("presence.permissions.denied")}
							</span>
						</li>
					))}
				</ul>
			</section>

			{context ? (
				<section className={styles.section}>
					<h2>{t("presence.context.title")}</h2>
					<div className={styles.card}>
						<p>
							<strong>{t("presence.context.identity")}:</strong>{" "}
							{context.identityLabel}
						</p>
						<p>
							{t("presence.context.concepts", { count: context.conceptCount })}
						</p>
						<p>
							<strong>{t("presence.context.mission")}:</strong>{" "}
							{context.activeMissionTitle ?? t("presence.context.none")}
						</p>
						<p>
							<strong>{t("presence.context.hint")}:</strong>{" "}
							{context.executiveHint ?? t("presence.context.none")}
						</p>
						<p>
							<strong>{t("presence.context.conversation")}:</strong>{" "}
							{context.conversationSessionId ?? t("presence.context.none")}
						</p>
					</div>
				</section>
			) : null}

			<section className={styles.section}>
				<h2>{t("presence.history.title")}</h2>
				{history && history.events.length > 0 ? (
					<ul className={styles.list}>
						{history.events.map((event) => (
							<li key={event.id} className={styles.listItem}>
								<strong>{event.label}</strong>
								<span className={styles.meta}>{event.detail}</span>
								<span className={styles.meta}>
									{event.surface} ·{" "}
									{new Date(event.recordedAt).toLocaleString()}
								</span>
							</li>
						))}
					</ul>
				) : (
					<p className={styles.meta}>{t("presence.history.empty")}</p>
				)}
			</section>

			<section className={styles.section}>
				<h2>{t("presence.explain.title")}</h2>
				{explain ? (
					<div className={styles.card}>
						<p>{t("presence.explain.reason", { reason: explain.reason })}</p>
						<p>{t("presence.explain.detail", { detail: explain.detail })}</p>
						<button
							type="button"
							onClick={() => void presenceService.getExplain().then(setExplain)}
						>
							{t("presence.explain.refresh")}
						</button>
					</div>
				) : null}
			</section>

			<section className={styles.section}>
				<h2>{t("presence.launcher.title")}</h2>
				<div className={styles.card}>
					<p className={styles.meta}>{t("presence.launcher.description")}</p>
					<Link className={styles.linkButton} to="/settings/shadow/brain">
						{t("presence.launcher.openBrain")}
					</Link>
				</div>
			</section>
		</div>
	);
}
