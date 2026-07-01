import { useCallback, useEffect, useState } from "react";
import { LearningCenter } from "@/features/learning/LearningCenter";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n";
import { learningService } from "@/services/learning/LearningService";
import type { LearningRecommendationsResponse } from "@/services/learning/types";

export function LearningSettingsPage() {
	const { t } = useTranslation();
	const [data, setData] = useState<LearningRecommendationsResponse | null>(
		null,
	);
	const [isUpdating, setIsUpdating] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const response = await learningService.getRecommendations();
		setData(response);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("learning.errors.loadFailed"));
		});
	}, [load, t]);

	const handleToggle = async (enabled: boolean) => {
		setIsUpdating(true);
		try {
			await learningService.updatePreferences({
				adaptiveRecommendationsEnabled: enabled,
			});
			await load();
		} catch {
			setError(t("learning.errors.updateFailed"));
		} finally {
			setIsUpdating(false);
		}
	};

	const handleReset = async () => {
		setIsUpdating(true);
		try {
			await learningService.reset();
			await load();
		} catch {
			setError(t("learning.errors.resetFailed"));
		} finally {
			setIsUpdating(false);
		}
	};

	return (
		<section>
			<PageIntroduction
				eyebrow={t("learning.eyebrow")}
				title={t("learning.title")}
				description={t("learning.description")}
				whatCanIDo={t("learning.whatCanIDo")}
			/>
			{error ? <p role="alert">{error}</p> : null}
			{data ? (
				<LearningCenter
					data={data}
					onToggleAdaptive={handleToggle}
					onReset={handleReset}
					isUpdating={isUpdating}
				/>
			) : (
				<p>{t("common.loading")}</p>
			)}
		</section>
	);
}
