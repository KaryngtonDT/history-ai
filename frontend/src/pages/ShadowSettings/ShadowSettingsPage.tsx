import { useCallback, useEffect, useState } from "react";
import { Link, useLocation } from "react-router";
import { PageIntroduction } from "@/features/product";
import { ShadowIdentityCenter } from "@/features/shadowIdentity/ShadowIdentityCenter";
import { ShadowKnowledgeCenter } from "@/features/shadowKnowledge/ShadowKnowledgeCenter";
import { ShadowMemoryCenter } from "@/features/shadowMemory/ShadowMemoryCenter";
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
	| "knowledge";

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
