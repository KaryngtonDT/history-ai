import { useEffect, useState } from "react";
import { Link } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import styles from "./AIDirectorTeaser.module.css";

export function AIDirectorTeaser() {
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
				AI Director
			</h2>
			<Card className={styles.card}>
				{loading ? (
					<Spinner label="Loading recommendation" />
				) : recommendation ? (
					<>
						<p className={styles.summary}>
							Recommended workflow: <strong>{recommendation.strategy}</strong>
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
							Configure on upload →
						</Link>
					</>
				) : (
					<>
						<p className={styles.detail}>
							Upload a video in automatic mode to see AI pipeline
							recommendations.
						</p>
						<Link to="/video/upload" className={styles.link}>
							Upload video →
						</Link>
					</>
				)}
			</Card>
		</section>
	);
}
