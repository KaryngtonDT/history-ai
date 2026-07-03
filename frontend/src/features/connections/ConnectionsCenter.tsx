import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { mobileService } from "@/services/mobile/MobileService";
import type {
	MobileConnection,
	MobileConnectionMode,
} from "@/services/mobile/types";
import styles from "../mobile/mobile.module.css";

const MODES: MobileConnectionMode[] = [
	"localhost",
	"lan",
	"auto",
	"tailscale",
	"cloud",
];

export function ConnectionsCenter() {
	const { t } = useTranslation();
	const [connection, setConnection] = useState<MobileConnection | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [saving, setSaving] = useState(false);

	const load = useCallback(async () => {
		setError(null);
		const response = await mobileService.getConnections();
		setConnection(response.connection);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("connections.errors.loadFailed"));
		});
	}, [load, t]);

	const save = async () => {
		if (!connection) {
			return;
		}

		setSaving(true);
		setError(null);
		try {
			const updated = await mobileService.updateConnection({
				mode: connection.mode,
				localhostUrl: connection.localhostUrl,
				lanUrl: connection.lanUrl,
				tailscaleUrl: connection.tailscaleUrl,
				homeWifiSsids: connection.homeWifiSsids,
			});
			setConnection(updated);
		} catch {
			setError(t("connections.errors.saveFailed"));
		} finally {
			setSaving(false);
		}
	};

	if (!connection) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div>
			{error ? <p role="alert">{error}</p> : null}
			<section className={styles.mobileSection}>
				<h3>{t("connections.mode.title")}</h3>
				<div className={styles.mobileRadioGroup}>
					{MODES.map((mode) => (
						<label key={mode}>
							<input
								type="radio"
								name="connectionMode"
								checked={connection.mode === mode}
								disabled={mode === "cloud"}
								onChange={() => setConnection({ ...connection, mode })}
							/>
							{t(`connections.mode.${mode}`)}
						</label>
					))}
				</div>
			</section>

			<section className={styles.mobileSection}>
				<h3>{t("connections.endpoints.title")}</h3>
				<label className={styles.mobileField}>
					<span>{t("connections.endpoints.localhost")}</span>
					<input
						value={connection.localhostUrl}
						onChange={(event) =>
							setConnection({
								...connection,
								localhostUrl: event.target.value,
							})
						}
					/>
				</label>
				<label className={styles.mobileField}>
					<span>{t("connections.endpoints.lan")}</span>
					<input
						value={connection.lanUrl}
						onChange={(event) =>
							setConnection({ ...connection, lanUrl: event.target.value })
						}
					/>
				</label>
				<label className={styles.mobileField}>
					<span>{t("connections.endpoints.tailscale")}</span>
					<input
						value={connection.tailscaleUrl}
						onChange={(event) =>
							setConnection({
								...connection,
								tailscaleUrl: event.target.value,
							})
						}
					/>
				</label>
				<label className={styles.mobileField}>
					<span>{t("connections.endpoints.homeWifi")}</span>
					<input
						value={connection.homeWifiSsids.join(", ")}
						onChange={(event) =>
							setConnection({
								...connection,
								homeWifiSsids: event.target.value
									.split(",")
									.map((ssid) => ssid.trim())
									.filter(Boolean),
							})
						}
					/>
				</label>
				<div className={styles.mobileActions}>
					<button type="button" disabled={saving} onClick={save}>
						{t("connections.actions.save")}
					</button>
				</div>
			</section>
		</div>
	);
}
