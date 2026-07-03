import { useCallback, useEffect, useState } from "react";
import { Link, useLocation } from "react-router";
import { BrowserCenter } from "@/features/browser/BrowserCenter";
import { MobileCenter } from "@/features/mobile";
import { PresenceCenter } from "@/features/presence/PresenceCenter";
import { PageIntroduction } from "@/features/product";
import { SecondBrainCenter } from "@/features/shadowBrain/SecondBrainCenter";
import { ExecutiveCenter } from "@/features/shadowExecutive/ExecutiveCenter";
import { ShadowIdentityCenter } from "@/features/shadowIdentity/ShadowIdentityCenter";
import { ShadowKnowledgeCenter } from "@/features/shadowKnowledge/ShadowKnowledgeCenter";
import { ShadowMemoryCenter } from "@/features/shadowMemory/ShadowMemoryCenter";
import { MentorCenter } from "@/features/shadowMentor/MentorCenter";
import { ShadowRelationshipCenter } from "@/features/shadowRelationship/ShadowRelationshipCenter";
import { ShadowTeachingCenter } from "@/features/shadowTeaching/ShadowTeachingCenter";
import { useTranslation } from "@/i18n";
import { shadowIdentityService } from "@/services/shadowIdentity/ShadowIdentityService";
import type { ShadowIdentityProfile } from "@/services/shadowIdentity/types";
import styles from "./ShadowSettingsPage.module.css";

type ShadowTab =
	| "identity"
	| "relationship"
	| "memory"
	| "teaching"
	| "knowledge"
	| "mentor"
	| "executive"
	| "brain"
	| "presence"
	| "browser"
	| "mobile";

function resolveTab(pathname: string): ShadowTab {
	if (pathname.endsWith("/relationship")) {
		return "relationship";
	}

	if (pathname.endsWith("/memory")) {
		return "memory";
	}

	if (pathname.endsWith("/teaching")) {
		return "teaching";
	}

	if (pathname.endsWith("/knowledge")) {
		return "knowledge";
	}

	if (pathname.endsWith("/mentor")) {
		return "mentor";
	}

	if (pathname.endsWith("/executive")) {
		return "executive";
	}

	if (pathname.endsWith("/brain")) {
		return "brain";
	}

	if (pathname.endsWith("/presence")) {
		return "presence";
	}

	if (pathname.endsWith("/browser")) {
		return "browser";
	}

	if (pathname.endsWith("/mobile")) {
		return "mobile";
	}

	return "identity";
}

export function ShadowSettingsPage() {
	const { t } = useTranslation();
	const location = useLocation();
	const activeTab = resolveTab(location.pathname);
	const [profile, setProfile] = useState<ShadowIdentityProfile | null>(null);
	const [isUpdating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const response = await shadowIdentityService.getProfile();
		setProfile(response);
	}, []);

	useEffect(() => {
		if (activeTab === "identity") {
			void load().catch(() => {
				setError(t("shadowIdentity.errors.loadFailed"));
			});
		}
	}, [activeTab, load, t]);

	const title =
		activeTab === "relationship"
			? t("shadowRelationship.title")
			: activeTab === "memory"
				? t("shadowMemory.title")
				: activeTab === "teaching"
					? t("shadowTeaching.title")
					: activeTab === "knowledge"
						? t("shadowKnowledge.title")
						: activeTab === "mentor"
							? t("shadowMentor.title")
							: activeTab === "executive"
								? t("shadowExecutive.title")
								: activeTab === "brain"
									? t("shadowBrain.title")
									: activeTab === "presence"
										? t("presence.title")
										: activeTab === "browser"
											? t("browser.title")
											: activeTab === "mobile"
												? t("mobile.title")
												: t("shadowIdentity.title");

	const description =
		activeTab === "relationship"
			? t("shadowRelationship.description")
			: activeTab === "memory"
				? t("shadowMemory.description")
				: activeTab === "teaching"
					? t("shadowTeaching.description")
					: activeTab === "knowledge"
						? t("shadowKnowledge.description")
						: activeTab === "mentor"
							? t("shadowMentor.description")
							: activeTab === "executive"
								? t("shadowExecutive.description")
								: activeTab === "brain"
									? t("shadowBrain.description")
									: activeTab === "presence"
										? t("presence.description")
										: activeTab === "browser"
											? t("browser.description")
											: activeTab === "mobile"
												? t("mobile.description")
												: t("shadowIdentity.description");

	const whatCanIDo =
		activeTab === "relationship"
			? t("shadowRelationship.whatCanIDo")
			: activeTab === "memory"
				? t("shadowMemory.whatCanIDo")
				: activeTab === "teaching"
					? t("shadowTeaching.whatCanIDo")
					: activeTab === "knowledge"
						? t("shadowKnowledge.whatCanIDo")
						: activeTab === "mentor"
							? t("shadowMentor.whatCanIDo")
							: activeTab === "executive"
								? t("shadowExecutive.whatCanIDo")
								: activeTab === "brain"
									? t("shadowBrain.whatCanIDo")
									: activeTab === "presence"
										? t("presence.whatCanIDo")
										: activeTab === "browser"
											? t("browser.whatCanIDo")
											: activeTab === "mobile"
												? t("mobile.whatCanIDo")
												: t("shadowIdentity.whatCanIDo");

	return (
		<section>
			<PageIntroduction
				eyebrow={t("shadowIdentity.eyebrow")}
				title={title}
				description={description}
				whatCanIDo={whatCanIDo}
			/>
			<div className={styles.tabs}>
				<Link
					to="/settings/shadow"
					className={activeTab === "identity" ? styles.tabActive : styles.tab}
				>
					{t("shadowRelationship.tabs.identity")}
				</Link>
				<Link
					to="/settings/shadow/relationship"
					className={
						activeTab === "relationship" ? styles.tabActive : styles.tab
					}
				>
					{t("shadowRelationship.tabs.relationship")}
				</Link>
				<Link
					to="/settings/shadow/memory"
					className={activeTab === "memory" ? styles.tabActive : styles.tab}
				>
					{t("shadowMemory.tabs.memory")}
				</Link>
				<Link
					to="/settings/shadow/teaching"
					className={activeTab === "teaching" ? styles.tabActive : styles.tab}
				>
					{t("shadowTeaching.tabs.teaching")}
				</Link>
				<Link
					to="/settings/shadow/knowledge"
					className={activeTab === "knowledge" ? styles.tabActive : styles.tab}
				>
					{t("shadowKnowledge.tabs.knowledge")}
				</Link>
				<Link
					to="/settings/shadow/mentor"
					className={activeTab === "mentor" ? styles.tabActive : styles.tab}
				>
					{t("shadowMentor.tabs.mentor")}
				</Link>
				<Link
					to="/settings/shadow/executive"
					className={activeTab === "executive" ? styles.tabActive : styles.tab}
				>
					{t("shadowExecutive.tabs.executive")}
				</Link>
				<Link
					to="/settings/shadow/brain"
					className={activeTab === "brain" ? styles.tabActive : styles.tab}
				>
					{t("shadowBrain.tabs.brain")}
				</Link>
				<Link
					to="/settings/shadow/presence"
					className={activeTab === "presence" ? styles.tabActive : styles.tab}
				>
					{t("presence.tabs.presence")}
				</Link>
				<Link
					to="/settings/shadow/browser"
					className={activeTab === "browser" ? styles.tabActive : styles.tab}
				>
					{t("browser.tabs.browser")}
				</Link>
				<Link
					to="/settings/shadow/mobile"
					className={activeTab === "mobile" ? styles.tabActive : styles.tab}
				>
					{t("mobile.tabs.mobile")}
				</Link>
			</div>
			{error ? <p role="alert">{error}</p> : null}
			{activeTab === "relationship" ? (
				<ShadowRelationshipCenter />
			) : activeTab === "memory" ? (
				<ShadowMemoryCenter />
			) : activeTab === "teaching" ? (
				<ShadowTeachingCenter />
			) : activeTab === "knowledge" ? (
				<ShadowKnowledgeCenter />
			) : activeTab === "mentor" ? (
				<MentorCenter />
			) : activeTab === "executive" ? (
				<ExecutiveCenter />
			) : activeTab === "brain" ? (
				<SecondBrainCenter />
			) : activeTab === "presence" ? (
				<PresenceCenter />
			) : activeTab === "browser" ? (
				<BrowserCenter />
			) : activeTab === "mobile" ? (
				<MobileCenter />
			) : profile ? (
				<ShadowIdentityCenter
					profile={profile}
					onProfileChange={setProfile}
					isUpdating={isUpdating}
				/>
			) : (
				<p>{t("common.loading")}</p>
			)}
		</section>
	);
}
