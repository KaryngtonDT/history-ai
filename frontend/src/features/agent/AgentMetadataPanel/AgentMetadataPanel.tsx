import type { AgentExecution } from "@/services/agent/types";
import styles from "./AgentMetadataPanel.module.css";
import { buildAgentMetadataSections } from "./agentMetadataLabels";

interface AgentMetadataPanelProps {
	execution: AgentExecution;
}

export function AgentMetadataPanel({ execution }: AgentMetadataPanelProps) {
	const sections = buildAgentMetadataSections(execution.steps);

	if (sections.length === 0) {
		return null;
	}

	return (
		<section className={styles.agentMetadataPanel} aria-label="Agent metadata">
			<h3 className={styles.sectionTitle}>Metadata</h3>
			<div className={styles.sectionList}>
				{sections.map((section) => (
					<article
						key={`metadata-${section.tool}`}
						className={styles.metadataSection}
						aria-label={`${section.title} metadata`}
					>
						<h4 className={styles.metadataTitle}>{section.title}</h4>
						<dl className={styles.metadataList}>
							{section.entries.map((entry) => (
								<div key={entry.label} className={styles.metadataItem}>
									<dt className={styles.metadataLabel}>{entry.label}</dt>
									<dd className={styles.metadataValue}>{entry.value}</dd>
								</div>
							))}
						</dl>
					</article>
				))}
			</div>
		</section>
	);
}
