import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router";
import { useTranslation } from "@/i18n";
import { mobileService } from "@/services/mobile/MobileService";
import type {
	MobileHealth,
	MobileProfile,
	MobileToday,
} from "@/services/mobile/types";
import styles from "../mobile.module.css";

export function MobileCenter() {
	const { t } = useTranslation();
	const [profile, setProfile] = useState<MobileProfile | null>(null);
	const [today, setToday] = useState<MobileToday | null>(null);
	const [health, setHealth] = useState<MobileHealth | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [busy, setBusy] = useState(false);

	const load = useCallback(async () => {
		setError(null);
		const [profileResponse, todayResponse, healthResponse] = await Promise.all([
			mobileService.getProfile(),
			mobileService.getToday(),
			mobileService.getHealth(),
		]);
		setProfile(profileResponse);
		setToday(todayResponse);
		setHealth(healthResponse);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("mobile.errors.loadFailed"));
		});
	}, [load, t]);

	const registerDemoDevice = async () => {
		setBusy(true);
		setError(null);
		try {
			await mobileService.registerDevice({
				deviceId: "web-demo-device",
				platform: "web",
				name: "Lumen Web Demo",
			});
			await load();
		} catch {
			setError(t("mobile.errors.registerFailed"));
		} finally {
			setBusy(false);
		}
	};

	const syncNow = async () => {
		setBusy(true);
		setError(null);
		try {
			await mobileService.sync();
			await load();
		} catch {
			setError(t("mobile.errors.syncFailed"));
		} finally {
			setBusy(false);
		}
	};

	return (
		<div>
			{error ? <p role="alert">{error}</p> : null}

			<section className={styles.mobileSection}>
				<h3>{t("mobile.status.title")}</h3>
				{profile ? (
					<div className={styles.mobileGrid}>
						<div className={styles.mobileStatus}>
							<span
								className={`${styles.mobileBadge} ${profile.session.active ? styles.mobileBadgeOk : ""}`}
							>
								{profile.session.active
									? t("mobile.status.connected")
									: t("mobile.status.disconnected")}
							</span>
							<span className={styles.mobileBadge}>
								{t("mobile.status.mode", {
									mode: profile.connection.mode,
								})}
							</span>
							{health ? (
								<span className={styles.mobileBadge}>
									{t("mobile.status.server", { status: health.status })}
								</span>
							) : null}
						</div>
						{profile.device ? (
							<p>
								{t("mobile.status.device", {
									name: profile.device.name,
									platform: profile.device.platform,
								})}
							</p>
						) : (
							<p>{t("mobile.status.noDevice")}</p>
						)}
						<div className={styles.mobileActions}>
							<button
								type="button"
								disabled={busy}
								onClick={registerDemoDevice}
							>
								{t("mobile.actions.registerDemo")}
							</button>
							<button type="button" disabled={busy} onClick={syncNow}>
								{t("mobile.actions.sync")}
							</button>
						</div>
					</div>
				) : (
					<p>{t("common.loading")}</p>
				)}
			</section>

			<section className={styles.mobileSection}>
				<h3>{t("mobile.today.title")}</h3>
				{today ? (
					<div className={styles.mobileGrid}>
						<p>{today.summary}</p>
						{today.missions.length > 0 ? (
							<ul className={styles.mobileList}>
								{today.missions.map((mission) => (
									<li key={mission.id}>{mission.title}</li>
								))}
							</ul>
						) : (
							<p>{t("mobile.today.noMissions")}</p>
						)}
						{today.revisions.length > 0 ? (
							<ul className={styles.mobileList}>
								{today.revisions.map((revision) => (
									<li key={revision.conceptKey}>{revision.label}</li>
								))}
							</ul>
						) : (
							<p>{t("mobile.today.noRevisions")}</p>
						)}
					</div>
				) : (
					<p>{t("common.loading")}</p>
				)}
			</section>

			<div className={styles.mobileLinkRow}>
				<Link to="/settings/connections">{t("connections.title")}</Link>
				<Link to="/settings/server">{t("server.title")}</Link>
			</div>
		</div>
	);
}
