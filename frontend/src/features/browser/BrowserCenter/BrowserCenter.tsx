import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router";
import { useTranslation } from "@/i18n";
import { browserService } from "@/services/browser/BrowserService";
import type {
	BrowserContext,
	BrowserExplain,
	BrowserHistory,
	BrowserPermission,
	BrowserPermissionsResponse,
	BrowserPlatformResult,
	BrowserSessionResponse,
	BrowserSitePolicy,
} from "@/services/browser/types";
import styles from "../browser.module.css";

const DEMO_VIDEO_ID = "video-docker-fundamentals-1";

function findYoutubePolicy(
	permissions: BrowserPermissionsResponse | null,
): BrowserSitePolicy | null {
	if (!permissions) {
		return null;
	}

	return (
		permissions.sitePolicies.find((policy) => policy.host === "youtube.com") ??
		null
	);
}

export function BrowserCenter() {
	const { t } = useTranslation();
	const [session, setSession] = useState<BrowserSessionResponse | null>(null);
	const [permissions, setPermissions] =
		useState<BrowserPermissionsResponse | null>(null);
	const [youtubePolicy, setYoutubePolicy] = useState<BrowserSitePolicy | null>(
		null,
	);
	const [history, setHistory] = useState<BrowserHistory | null>(null);
	const [explain, setExplain] = useState<BrowserExplain | null>(null);
	const [context, setContext] = useState<BrowserContext | null>(null);
	const [platformUrl, setPlatformUrl] = useState(
		"https://www.youtube.com/watch?v=demo",
	);
	const [platformResult, setPlatformResult] =
		useState<BrowserPlatformResult | null>(null);
	const [readingUrl, setReadingUrl] = useState(
		"https://en.wikipedia.org/wiki/Docker_(software)",
	);
	const [readingTitle, setReadingTitle] = useState("Docker (software)");
	const [readingSelection, setReadingSelection] = useState(
		"Docker is a set of platform as a service products.",
	);
	const [error, setError] = useState<string | null>(null);
	const [saving, setSaving] = useState(false);
	const [detecting, setDetecting] = useState(false);
	const [sendingContext, setSendingContext] = useState(false);

	const load = useCallback(async () => {
		setError(null);
		const [
			sessionResponse,
			permissionsResponse,
			historyResponse,
			explainResponse,
		] = await Promise.all([
			browserService.getSession(),
			browserService.getPermissions(),
			browserService.getHistory(20),
			browserService.getExplain(),
		]);
		setSession(sessionResponse);
		setPermissions(permissionsResponse);
		setYoutubePolicy(findYoutubePolicy(permissionsResponse));
		setHistory(historyResponse);
		setExplain(explainResponse);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("browser.errors.loadFailed"));
		});
	}, [load, t]);

	async function handleConnect() {
		const workspace = await browserService.connect({});
		setSession(workspace.session);
	}

	async function handleDisconnect() {
		const workspace = await browserService.disconnect();
		setSession(workspace.session);
	}

	async function handleDetectPlatform() {
		setDetecting(true);
		try {
			const result = await browserService.detectPlatform({ url: platformUrl });
			setPlatformResult(result);
		} finally {
			setDetecting(false);
		}
	}

	function toggleYoutubePermission(
		permission: BrowserPermission,
		granted: boolean,
	) {
		if (!youtubePolicy) {
			return;
		}

		setYoutubePolicy({
			...youtubePolicy,
			permissions: youtubePolicy.permissions.map((entry) =>
				entry.permission === permission ? { ...entry, granted } : entry,
			),
		});
	}

	function toggleYoutubeAllowed(allowed: boolean) {
		if (!youtubePolicy) {
			return;
		}

		setYoutubePolicy({ ...youtubePolicy, allowed });
	}

	async function handleSavePermissions() {
		if (!youtubePolicy || !permissions) {
			return;
		}

		setSaving(true);
		try {
			const otherPolicies = permissions.sitePolicies.filter(
				(policy) => policy.host !== "youtube.com",
			);
			const response = await browserService.updatePermissions({
				sitePolicies: [...otherPolicies, youtubePolicy],
			});
			setPermissions(response);
			setYoutubePolicy(findYoutubePolicy(response));
		} finally {
			setSaving(false);
		}
	}

	async function handleSendReadingContext() {
		setSendingContext(true);
		try {
			const response = await browserService.updateContext({
				url: readingUrl,
				title: readingTitle,
				tabId: "demo-tab-reading",
				selection: readingSelection,
			});
			setSession(response.session);
			setContext(response.context.context);
		} finally {
			setSendingContext(false);
		}
	}

	if (error) {
		return <p role="alert">{error}</p>;
	}

	if (!session || !permissions) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.browser}>
			<section className={styles.section}>
				<h2>{t("browser.status.title")}</h2>
				<div className={styles.card}>
					<p>
						{session.active && session.session
							? t("browser.status.active", { state: session.session.state })
							: t("browser.status.inactive")}
					</p>
					{session.session ? (
						<p className={styles.meta}>
							{t("browser.status.lastActive", {
								time: new Date(session.session.lastActiveAt).toLocaleString(),
							})}
						</p>
					) : null}
					<div className={styles.actions}>
						<button type="button" onClick={() => void handleConnect()}>
							{t("browser.status.connect")}
						</button>
						<button type="button" onClick={() => void handleDisconnect()}>
							{t("browser.status.disconnect")}
						</button>
					</div>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("browser.platform.title")}</h2>
				<div className={styles.card}>
					<div className={styles.fieldRow}>
						<input
							className={styles.input}
							value={platformUrl}
							onChange={(event) => setPlatformUrl(event.target.value)}
							placeholder={t("browser.platform.urlPlaceholder")}
						/>
						<button
							type="button"
							disabled={detecting}
							onClick={() => void handleDetectPlatform()}
						>
							{t("browser.platform.detect")}
						</button>
					</div>
					{platformResult ? (
						<p className={styles.meta}>
							{t("browser.platform.result", {
								platform: platformResult.platform,
								host: platformResult.host,
							})}
						</p>
					) : null}
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("browser.permissions.title")}</h2>
				{youtubePolicy ? (
					<div className={styles.policyCard}>
						<strong>youtube.com</strong>
						<label className={styles.checkboxLabel}>
							<input
								type="checkbox"
								checked={youtubePolicy.allowed}
								onChange={(event) => toggleYoutubeAllowed(event.target.checked)}
							/>
							{t("browser.permissions.siteAllowed")}
						</label>
						<div className={styles.permissionGrid}>
							{youtubePolicy.permissions.map((entry) => (
								<label key={entry.permission} className={styles.checkboxLabel}>
									<input
										type="checkbox"
										checked={entry.granted}
										onChange={(event) =>
											toggleYoutubePermission(
												entry.permission,
												event.target.checked,
											)
										}
									/>
									{entry.permission.replace(/_/g, " ")}
								</label>
							))}
						</div>
						<button
							type="button"
							disabled={saving}
							onClick={() => void handleSavePermissions()}
						>
							{t("browser.permissions.save")}
						</button>
					</div>
				) : (
					<p className={styles.meta}>{t("browser.permissions.noYoutube")}</p>
				)}
				{permissions.sitePolicies.length > 0 ? (
					<ul className={styles.list}>
						{permissions.sitePolicies.map((policy) => (
							<li key={policy.host} className={styles.listItem}>
								<strong>{policy.host}</strong>
								<span className={styles.meta}>
									{policy.allowed
										? t("browser.permissions.allowed")
										: t("browser.permissions.blocked")}
								</span>
							</li>
						))}
					</ul>
				) : null}
			</section>

			<section className={styles.section}>
				<h2>{t("browser.history.title")}</h2>
				{history && history.activities.length > 0 ? (
					<ul className={styles.list}>
						{history.activities.map((activity) => (
							<li key={activity.id} className={styles.listItem}>
								<strong>{activity.label}</strong>
								<span className={styles.meta}>{activity.detail}</span>
								<span className={styles.meta}>
									{activity.platform} ·{" "}
									{new Date(activity.recordedAt).toLocaleString()}
								</span>
							</li>
						))}
					</ul>
				) : (
					<p className={styles.meta}>{t("browser.history.empty")}</p>
				)}
			</section>

			<section className={styles.section}>
				<h2>{t("browser.explain.title")}</h2>
				{explain ? (
					<div className={styles.card}>
						<p>{t("browser.explain.reason", { reason: explain.reason })}</p>
						<p>{t("browser.explain.detail", { detail: explain.detail })}</p>
						{explain.humanReadable ? (
							<p className={styles.meta}>{explain.humanReadable}</p>
						) : null}
						<button
							type="button"
							onClick={() => void browserService.getExplain().then(setExplain)}
						>
							{t("browser.explain.refresh")}
						</button>
					</div>
				) : null}
			</section>

			<section className={styles.section}>
				<h2>{t("browser.reading.title")}</h2>
				<div className={styles.card}>
					<p className={styles.meta}>{t("browser.reading.description")}</p>
					<div className={styles.fieldRow}>
						<span>{t("browser.reading.url")}</span>
						<input
							className={styles.input}
							value={readingUrl}
							onChange={(event) => setReadingUrl(event.target.value)}
						/>
					</div>
					<div className={styles.fieldRow}>
						<span>{t("browser.reading.pageTitle")}</span>
						<input
							className={styles.input}
							value={readingTitle}
							onChange={(event) => setReadingTitle(event.target.value)}
						/>
					</div>
					<label>
						<span>{t("browser.reading.selection")}</span>
						<textarea
							className={styles.textarea}
							value={readingSelection}
							onChange={(event) => setReadingSelection(event.target.value)}
						/>
					</label>
					<button
						type="button"
						disabled={sendingContext}
						onClick={() => void handleSendReadingContext()}
					>
						{t("browser.reading.send")}
					</button>
					{context ? (
						<p className={styles.meta}>
							{t("browser.reading.contextPreview", {
								platform: context.platform,
								selection: context.selection ?? "",
							})}
						</p>
					) : null}
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("browser.youtube.title")}</h2>
				<div className={styles.card}>
					<p className={styles.meta}>{t("browser.youtube.description")}</p>
					<Link
						className={styles.linkButton}
						to={`/video/${DEMO_VIDEO_ID}/watch`}
					>
						{t("browser.youtube.openWatch")}
					</Link>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("browser.extension.title")}</h2>
				<div className={styles.card}>
					<p className={styles.meta}>{t("browser.extension.description")}</p>
					<span className={styles.badge}>
						{t("browser.extension.comingSoon")}
					</span>
				</div>
			</section>
		</div>
	);
}
