import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { PageIntroduction } from "@/features/product";
import { useTranslation } from "@/i18n";
import type { WorkItemSummary } from "@/services/workItem/types";
import { workItemService } from "@/services/workItem/WorkItemService";
import { ActionableStats } from "../ActionableStats";
import { AIDirectorTeaser } from "../AIDirectorTeaser";
import { ContinueWork } from "../ContinueWork";
import { CreateSection } from "../CreateSection";
import { RecentWorkList } from "../RecentWorkList";
import styles from "./HomeMissionControl.module.css";

export function HomeMissionControl() {
	const { t } = useTranslation();
	const [summary, setSummary] = useState<WorkItemSummary | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		void workItemService
			.getSummary()
			.then((data) => {
				setSummary(data);
				setLoadError(null);
			})
			.catch(() => {
				setSummary({
					recentWork: [],
					continueWork: null,
					videoCount: 0,
					projectCount: 0,
					completedCount: 0,
					artifactCount: 0,
				});
				setLoadError(t("home.loadError"));
			});
	}, [t]);

	if (summary === null) {
		return (
			<div className={styles.root}>
				<PageIntroduction
					eyebrow={t("home.eyebrow")}
					title={t("home.title")}
					description={t("home.description")}
				/>
				<div className={styles.loading}>
					<Spinner label={t("home.loading")} />
				</div>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("home.eyebrow")}
				title={t("home.title")}
				description={t("home.description")}
				whatCanIDo={t("home.whatCanIDo")}
			/>

			{loadError !== null ? (
				<EmptyState title={t("home.errorTitle")} description={loadError} />
			) : null}

			<CreateSection />

			{summary.continueWork ? (
				<ContinueWork item={summary.continueWork} />
			) : null}

			<div className={styles.columns}>
				<RecentWorkList items={summary.recentWork} />
				<div className={styles.side}>
					<ActionableStats summary={summary} />
					<AIDirectorTeaser />
				</div>
			</div>
		</div>
	);
}
