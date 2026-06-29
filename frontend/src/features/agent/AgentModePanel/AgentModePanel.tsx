import { type FormEvent, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { AgentExecutionTrace } from "@/features/agent/AgentExecutionTrace";
import { agentService } from "@/services/agent/AgentService";
import type { AgentExecution } from "@/services/agent/types";
import styles from "./AgentModePanel.module.css";

interface AgentModePanelProps {
	contentId: string;
	conversationId?: string | null;
}

type AgentViewState =
	| { status: "idle" }
	| { status: "loading" }
	| { status: "ready"; execution: AgentExecution }
	| { status: "error" };

export function AgentModePanel({
	contentId,
	conversationId = null,
}: AgentModePanelProps) {
	const [question, setQuestion] = useState("");
	const [viewState, setViewState] = useState<AgentViewState>({
		status: "idle",
	});

	const trimmedQuestion = question.trim();
	const canRun = trimmedQuestion.length > 0;

	async function runAgent(): Promise<void> {
		if (!canRun) {
			return;
		}

		setViewState({ status: "loading" });

		try {
			const execution = await agentService.runAgent(
				contentId,
				trimmedQuestion,
				conversationId ?? undefined,
			);

			if (execution.steps.length === 0) {
				setViewState({ status: "error" });
				return;
			}

			setViewState({ status: "ready", execution });
		} catch {
			setViewState({ status: "error" });
		}
	}

	function handleSubmit(event: FormEvent<HTMLFormElement>): void {
		event.preventDefault();
		void runAgent();
	}

	return (
		<Card className={styles.agentModePanel}>
			<p className={styles.label}>Agent Mode</p>
			<form className={styles.agentForm} onSubmit={handleSubmit}>
				<label className={styles.questionField}>
					<span className={styles.questionLabel}>Question</span>
					<input
						className={styles.questionInput}
						type="text"
						value={question}
						onChange={(event) => {
							setQuestion(event.target.value);
						}}
						placeholder="Compare Rome and Byzantium"
					/>
				</label>
				<Button
					type="submit"
					disabled={!canRun || viewState.status === "loading"}
				>
					Run agent
				</Button>
			</form>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Running agent workflow" />
				</div>
			) : null}
			{viewState.status === "error" ? (
				<p className={styles.errorMessage} role="alert">
					Unable to run the agent workflow for this question.
				</p>
			) : null}
			{viewState.status === "ready" ? (
				<AgentExecutionTrace execution={viewState.execution} />
			) : null}
			{viewState.status === "idle" ? (
				<EmptyState
					className={styles.emptyState}
					title="No agent run yet"
					description="Ask a question to generate a deterministic agent plan and execution trace."
				/>
			) : null}
		</Card>
	);
}
