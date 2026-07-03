import { useCallback, useEffect, useState } from "react";
import { API_BASE_URL, LUMEN_WEB_BASE_URL } from "../app/config";
import { loadApiBaseUrl, saveApiBaseUrl } from "../auth/tokenStore";
import { connectDesktop, syncShadowProfile } from "../profile/syncProfile";

async function openLumen(path: string) {
	const url = `${LUMEN_WEB_BASE_URL}${path}`;

	if ("__TAURI_INTERNALS__" in window) {
		try {
			const { openUrl } = await import("@tauri-apps/plugin-opener");
			await openUrl(url);
			return;
		} catch {
			// Fall back to window.open when the opener plugin is unavailable.
		}
	}

	window.open(url, "_blank", "noopener,noreferrer");
}

export function QuickLauncher() {
	const [apiBaseUrl, setApiBaseUrl] = useState(loadApiBaseUrl() ?? API_BASE_URL);
	const [searchQuery, setSearchQuery] = useState("");
	const [profileLabel, setProfileLabel] = useState<string | null>(null);
	const [conceptCount, setConceptCount] = useState<number | null>(null);
	const [status, setStatus] = useState<string>("Ready");

	const refreshProfile = useCallback(async () => {
		saveApiBaseUrl(apiBaseUrl);
		await connectDesktop(apiBaseUrl);
		const profile = await syncShadowProfile(apiBaseUrl);
		setProfileLabel(profile.identityLabel);
		setConceptCount(profile.conceptCount);
		setStatus("Connected");
	}, [apiBaseUrl]);

	useEffect(() => {
		void refreshProfile().catch(() => {
			setStatus("Offline — check Lumen backend");
		});
	}, [refreshProfile]);

	return (
		<main style={{ fontFamily: "system-ui, sans-serif", padding: "1rem" }}>
			<h1>Shadow Quick Launcher</h1>
			<p>{status}</p>
			<label>
				Backend URL
				<input
					value={apiBaseUrl}
					onChange={(event) => setApiBaseUrl(event.target.value)}
					style={{ display: "block", width: "100%", marginTop: "0.25rem" }}
				/>
			</label>
			<button type="button" onClick={() => void refreshProfile()}>
				Reconnect
			</button>
			{profileLabel ? (
				<p>
					{profileLabel} · {conceptCount ?? 0} concepts
				</p>
			) : null}
			<label>
				Search Second Brain
				<input
					value={searchQuery}
					onChange={(event) => setSearchQuery(event.target.value)}
					placeholder="docker, kubernetes…"
					style={{ display: "block", width: "100%", marginTop: "0.25rem" }}
				/>
			</label>
			<div style={{ display: "flex", gap: "0.5rem", marginTop: "0.75rem" }}>
				<button
					type="button"
					onClick={() => void openLumen(
						`/settings/shadow/brain?q=${encodeURIComponent(searchQuery)}`,
					)}
				>
					Open concept search
				</button>
				<button type="button" onClick={() => void openLumen("/settings/shadow/mentor")}>
					Resume mission
				</button>
				<button type="button" onClick={() => void openLumen("/settings/shadow/presence")}>
					Presence settings
				</button>
			</div>
		</main>
	);
}
