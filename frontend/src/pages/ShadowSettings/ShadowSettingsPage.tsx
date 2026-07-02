import { useCallback, useEffect, useState } from "react";
import { PageIntroduction } from "@/features/product";
import { ShadowIdentityCenter } from "@/features/shadowIdentity/ShadowIdentityCenter";
import { useTranslation } from "@/i18n";
import { shadowIdentityService } from "@/services/shadowIdentity/ShadowIdentityService";
import type { ShadowIdentityProfile } from "@/services/shadowIdentity/types";

export function ShadowSettingsPage() {
	const { t } = useTranslation();
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
				title={t("shadowIdentity.title")}
				description={t("shadowIdentity.description")}
				whatCanIDo={t("shadowIdentity.whatCanIDo")}
			/>
			{error ? <p role="alert">{error}</p> : null}
			{profile ? (
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
