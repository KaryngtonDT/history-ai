import { useEffect, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import {
	AIProviderList,
	getEngineSectionLabel,
} from "@/features/ai/AIProviderList";
import { aiEngineService } from "@/services/ai/AIEngineService";
import type { AIEngine } from "@/services/ai/types";
import styles from "./AIEngineSettings.module.css";

export function AIEngineSettings() {
	const [engines, setEngines] = useState<AIEngine[]>([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);

	useEffect(() => {
		void aiEngineService
			.listEngines()
			.then((loaded) => {
				setEngines(loaded);
				setLoading(false);
			})
			.catch(() => {
				setError("Unable to load AI engines.");
				setLoading(false);
			});
	}, []);

	if (loading) {
		return <Spinner label="Loading AI engines" />;
	}

	if (error) {
		return <p className={styles.error}>{error}</p>;
	}

	if (engines.length === 0) {
		return (
			<EmptyState
				title="No AI engines"
				description="The platform has not registered any AI providers yet."
			/>
		);
	}

	return (
		<div className={styles.root}>
			<div>
				<h2 className={styles.title}>AI Engines</h2>
				<p className={styles.description}>
					Read-only overview of registered speech, translation, voice, and
					lip-sync providers.
				</p>
			</div>

			<div className={styles.engineGrid}>
				{engines.map((engine) => (
					<Card key={engine.engineId} className={styles.engineCard}>
						<h3 className={styles.engineTitle}>
							{getEngineSectionLabel(engine)}
						</h3>
						<AIProviderList engine={engine} />
					</Card>
				))}
			</div>
		</div>
	);
}
