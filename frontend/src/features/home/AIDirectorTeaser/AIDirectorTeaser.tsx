import { useEffect, useState } from "react";
import { Link } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import styles from "./AIDirectorTeaser.module.css";

export function AIDirectorTeaser() {
	const { t } = useTranslation();
	const [recommendation, setRecommendation] =
		useState<PipelineRecommendation | null>(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		void orchestratorService
			.loadRecommendation()
			.then(setRecommendation)
			.catch(() => setRecommendation(null))
			.finally(() => setLoading(false));
	}, []);

	return (
		<section className={styles.root} aria-labelledby="ai-director-heading">
			<h2 id="ai-director-heading" className={styles.heading}>
				{t("home.aiDirector.heading")}
			</h2>
			<Card className={styles.card}>
				{loading ? (
					<Spinner label={t("home.aiDirector.loading")} />
				) : recommendation ? (
					<>
						<p className={styles.summary}>
							{t("home.aiDirector.recommended")}{" "}
							<strong>{recommendation.strategy}</strong>
						</p>
						<p className={styles.detail}>{recommendation.explanation}</p>
						{recommendation.reasons && recommendation.reasons.length > 0 ? (
							<ul className={styles.reasons}>
								{recommendation.reasons.slice(0, 2).map((reason) => (
									<li key={reason}>{reason}</li>
								))}
							</ul>
						) : null}
						<Link to="/video/upload" className={styles.link}>
							{t("home.aiDirector.configureLink")}
						</Link>
					</>
				) : (
					<>
						<p className={styles.detail}>{t("home.aiDirector.empty")}</p>
						<Link to="/video/upload" className={styles.link}>
							{t("home.aiDirector.uploadLink")}
						</Link>
					</>
				)}
			</Card>
		</section>
	);
}
