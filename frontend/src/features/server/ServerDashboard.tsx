import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { mobileService } from "@/services/mobile/MobileService";
import type { MobileHealth, MobileServer } from "@/services/mobile/types";
import styles from "../mobile/mobile.module.css";

export function ServerDashboard() {
	const { t } = useTranslation();
	const [server, setServer] = useState<MobileServer | null>(null);
	const [health, setHealth] = useState<MobileHealth | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [serverResponse, healthResponse] = await Promise.all([
			mobileService.getServer(),
			mobileService.getHealth(),
		]);
		setServer(serverResponse);
		setHealth(healthResponse);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("server.errors.loadFailed"));
		});
	}, [load, t]);

	if (!server || !health) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div>
			{error ? <p role="alert">{error}</p> : null}
			<section className={styles.mobileSection}>
				<h3>{t("server.overview.title")}</h3>
				<div className={styles.mobileStatus}>
					<span
						className={`${styles.mobileBadge} ${server.available ? styles.mobileBadgeOk : ""}`}
					>
						{t("server.overview.available", {
							value: server.available ? "yes" : "no",
						})}
					</span>
					<span className={styles.mobileBadge}>
						{t("server.overview.checks", {
							healthy: server.healthyCount,
							total: server.totalChecks,
						})}
					</span>
					<span className={styles.mobileBadge}>
						{t("server.overview.mode", { mode: health.connectionMode })}
					</span>
				</div>
				<p>
					{t("server.overview.status", {
						status: server.status,
						live: server.liveStatus,
					})}
				</p>
			</section>

			<section className={styles.mobileSection}>
				<h3>{t("server.checks.title")}</h3>
				{server.checks.length > 0 ? (
					<ul className={styles.mobileList}>
						{server.checks.map((check) => (
							<li key={check.label ?? "check"}>
								{check.label} — {check.ok ? "OK" : "FAIL"}
							</li>
						))}
					</ul>
				) : (
					<p>{t("server.checks.empty")}</p>
				)}
			</section>
		</div>
	);
}
