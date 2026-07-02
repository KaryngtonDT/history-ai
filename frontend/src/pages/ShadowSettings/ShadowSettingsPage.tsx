import { useCallback, useEffect, useState } from "react";
import { Link, useLocation } from "react-router";
import { PageIntroduction } from "@/features/product";
import { ShadowIdentityCenter } from "@/features/shadowIdentity/ShadowIdentityCenter";
import { ShadowRelationshipCenter } from "@/features/shadowRelationship/ShadowRelationshipCenter";
import { useTranslation } from "@/i18n";
import { shadowIdentityService } from "@/services/shadowIdentity/ShadowIdentityService";
import type { ShadowIdentityProfile } from "@/services/shadowIdentity/types";
import styles from "./ShadowSettingsPage.module.css";

export function ShadowSettingsPage() {
	const { t } = useTranslation();
	const location = useLocation();
	const isRelationship = location.pathname.endsWith("/relationship");
	const [profile, setProfile] = useState<ShadowIdentityProfile | null>(null);
	const [isUpdating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const response = await shadowIdentityService.getProfile();
		setProfile(response);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowIdentity.errors.loadFailed"));
		});
	}, [load, t]);

	return (
		<section>
			<PageIntroduction
				eyebrow={t("shadowIdentity.eyebrow")}
				title={
					isRelationship
						? t("shadowRelationship.title")
						: t("shadowIdentity.title")
				}
				description={
					isRelationship
						? t("shadowRelationship.description")
						: t("shadowIdentity.description")
				}
				whatCanIDo={
					isRelationship
						? t("shadowRelationship.whatCanIDo")
						: t("shadowIdentity.whatCanIDo")
				}
			/>
			<div className={styles.tabs}>
				<Link
					to="/settings/shadow"
					className={isRelationship ? styles.tab : styles.tabActive}
				>
					{t("shadowRelationship.tabs.identity")}
				</Link>
				<Link
					to="/settings/shadow/relationship"
					className={isRelationship ? styles.tabActive : styles.tab}
				>
					{t("shadowRelationship.tabs.relationship")}
				</Link>
			</div>
			{error ? <p role="alert">{error}</p> : null}
			{isRelationship ? (
				<ShadowRelationshipCenter />
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
