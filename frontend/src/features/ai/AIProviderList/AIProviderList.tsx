import { AI_CAPABILITY_LABELS, type AIEngine } from "@/services/ai/types";
import styles from "./AIProviderList.module.css";

interface AIProviderListProps {
	engine: AIEngine;
}

export function AIProviderList({ engine }: AIProviderListProps) {
	const enabledProviders = engine.providers.filter(
		(provider) => provider.enabled,
	);

	if (enabledProviders.length === 0) {
		return <p className={styles.comingSoon}>Coming soon</p>;
	}

	return (
		<ul className={styles.aiProviderList}>
			{enabledProviders.map((provider) => (
				<li key={provider.providerId} className={styles.providerItem}>
					<span className={styles.statusIcon} aria-hidden="true">
						✓
					</span>
					<span>{provider.displayName}</span>
				</li>
			))}
		</ul>
	);
}

export function getEngineSectionLabel(engine: AIEngine): string {
	return AI_CAPABILITY_LABELS[engine.capability];
}
