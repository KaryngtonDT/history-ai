import type { AgentExecution } from "@/services/agent/types";
import { formatAgentToolLabel } from "@/services/agent/types";
import styles from "./AgentExecutionTrace.module.css";

interface AgentExecutionTraceProps {
	execution: AgentExecution;
}

export function AgentExecutionTrace({ execution }: AgentExecutionTraceProps) {
	return (
		<div className={styles.agentExecutionTrace}>
			<section aria-label="Agent plan">
				<h3 className={styles.sectionTitle}>Plan</h3>
				<ol className={styles.stepList}>
					{execution.plan.map((step) => (
						<li
							key={`plan-${step.order}-${step.tool}`}
							className={styles.stepItem}
						>
							<div className={styles.stepHeader}>
								<span className={styles.stepOrder}>Step {step.order + 1}</span>
								<span className={styles.stepTool}>
									{formatAgentToolLabel(step.tool)}
								</span>
							</div>
							<p className={styles.stepDescription}>{step.description}</p>
						</li>
					))}
				</ol>
			</section>
			<section aria-label="Agent execution">
				<h3 className={styles.sectionTitle}>Execution</h3>
				<ol className={styles.stepList}>
					{execution.steps.map((step) => (
						<li
							key={`execution-${step.order}-${step.tool}`}
							className={styles.stepItem}
						>
							<div className={styles.stepHeader}>
								<span className={styles.stepOrder}>Step {step.order + 1}</span>
								<span className={styles.stepTool}>
									{formatAgentToolLabel(step.tool)}
								</span>
								<span className={styles.stepStatus}>{step.status}</span>
							</div>
							<p className={styles.stepSummary}>{step.summary}</p>
						</li>
					))}
				</ol>
			</section>
			<p className={styles.finalSummary}>{execution.finalSummary}</p>
		</div>
	);
}
