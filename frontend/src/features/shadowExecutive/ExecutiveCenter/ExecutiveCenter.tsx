import { useCallback, useEffect, useState } from "react";
import { Dialog } from "@/components/ui/Dialog";
import { useTranslation } from "@/i18n";
import { shadowExecutiveService } from "@/services/shadowExecutive/ShadowExecutiveService";
import type {
	DecisionEvidenceItem,
	DecisionExplanation,
	DecisionHistory,
	ExecutiveAgenda,
	ExecutiveDashboard,
	ExecutiveDecision,
	ExecutiveRecommendation,
	ExecutiveTask,
	WeeklyExecutiveReview,
} from "@/services/shadowExecutive/types";
import styles from "../shadowExecutive.module.css";

function parseEvidenceItem(raw: string): DecisionEvidenceItem {
	const separatorIndex = raw.indexOf(":");
	if (separatorIndex === -1) {
		return {
			source: "unknown",
			reference: raw,
			label: raw,
		};
	}

	const source = raw.slice(0, separatorIndex);
	const reference = raw.slice(separatorIndex + 1);
	const knownSources = [
		"knowledge",
		"memory",
		"teaching",
		"mentor",
		"session",
	] as const;

	return {
		source: knownSources.includes(source as (typeof knownSources)[number])
			? (source as DecisionEvidenceItem["source"])
			: "unknown",
		reference,
		label: reference.replace(/_/g, " "),
	};
}

function buildExplanation(decision: ExecutiveDecision): DecisionExplanation {
	return {
		decisionId: decision.id,
		type: decision.type,
		title: decision.title,
		summary: decision.summary,
		reason: decision.reason,
		evidence: decision.evidence.map(parseEvidenceItem),
		impacts: decision.impacts,
		linkedGoalId: decision.linkedGoalId,
		linkedGoalTitle: decision.linkedGoalId?.replace(/^goal-/, "") ?? null,
		linkedConceptKey: decision.linkedConceptKey,
		linkedResourceId: decision.linkedResourceId,
	};
}

function priorityClass(priority: string): string {
	switch (priority) {
		case "critical":
			return styles.priorityCritical;
		case "high":
			return styles.priorityHigh;
		case "low":
			return styles.priorityLow;
		default:
			return styles.priorityNormal;
	}
}

function AgendaPanel({ agenda }: { agenda: ExecutiveAgenda }) {
	const { t } = useTranslation();

	function renderTasks(tasks: ExecutiveTask[], emptyKey: string) {
		if (tasks.length === 0) {
			return <p>{t(emptyKey)}</p>;
		}

		return (
			<div className={styles.taskSteps}>
				{tasks.map((task) => (
					<article key={task.id} className={styles.taskStep}>
						<span className={styles.taskType}>
							{t(`shadowExecutive.taskTypes.${task.type}`)}
						</span>
						<strong>{task.label}</strong>
						<p className={styles.meta}>{task.detail}</p>
						{task.scheduledAt ? (
							<p className={styles.meta}>
								{new Date(task.scheduledAt).toLocaleString()}
							</p>
						) : null}
					</article>
				))}
			</div>
		);
	}

	return renderTasks(agenda.today, "shadowExecutive.empty.today");
}

function UpcomingPanel({ agenda }: { agenda: ExecutiveAgenda }) {
	const { t } = useTranslation();

	if (agenda.upcoming.length === 0) {
		return <p>{t("shadowExecutive.empty.upcoming")}</p>;
	}

	return (
		<div className={styles.taskSteps}>
			{agenda.upcoming.map((task) => (
				<article key={task.id} className={styles.taskStep}>
					<span className={styles.taskType}>
						{t(`shadowExecutive.taskTypes.${task.type}`)}
					</span>
					<strong>{task.label}</strong>
					<p className={styles.meta}>{task.detail}</p>
					{task.scheduledAt ? (
						<p className={styles.meta}>
							{new Date(task.scheduledAt).toLocaleString()}
						</p>
					) : null}
				</article>
			))}
		</div>
	);
}

function RecommendationsPanel({
	recommendations,
}: {
	recommendations: ExecutiveRecommendation[];
}) {
	const { t } = useTranslation();

	if (recommendations.length === 0) {
		return <p>{t("shadowExecutive.empty.recommendations")}</p>;
	}

	return (
		<ul className={styles.list}>
			{recommendations.map((item) => (
				<li key={item.id} className={styles.listItem}>
					<strong>{item.title}</strong>
					<p>{item.detail}</p>
					<p className={styles.meta}>
						{t("shadowExecutive.decision.typeLabel", { value: item.type })} ·{" "}
						<span className={priorityClass(item.priority)}>
							{t("shadowExecutive.priorityLabel", { value: item.priority })}
						</span>
					</p>
				</li>
			))}
		</ul>
	);
}

function DecisionHistoryPanel({
	history,
}: {
	history: DecisionHistory | null;
}) {
	const { t } = useTranslation();

	if (!history) {
		return <p>{t("shadowExecutive.empty.history")}</p>;
	}

	return (
		<>
			<div className={styles.statsGrid}>
				<article className={styles.statCard}>
					<span className={styles.statValue}>{history.stats.approved}</span>
					<span className={styles.meta}>
						{t("shadowExecutive.history.stats.approved")}
					</span>
				</article>
				<article className={styles.statCard}>
					<span className={styles.statValue}>{history.stats.rejected}</span>
					<span className={styles.meta}>
						{t("shadowExecutive.history.stats.rejected")}
					</span>
				</article>
				<article className={styles.statCard}>
					<span className={styles.statValue}>{history.stats.deferred}</span>
					<span className={styles.meta}>
						{t("shadowExecutive.history.stats.deferred")}
					</span>
				</article>
				<article className={styles.statCard}>
					<span className={styles.statValue}>{history.stats.ignored}</span>
					<span className={styles.meta}>
						{t("shadowExecutive.history.stats.ignored")}
					</span>
				</article>
			</div>
			{history.decisions.length > 0 ? (
				<ul className={styles.list}>
					{history.decisions.map((decision) => (
						<li key={decision.id} className={styles.listItem}>
							<strong>{decision.title}</strong>
							<p className={styles.meta}>
								{t("shadowExecutive.statusLabel", { value: decision.status })}
							</p>
						</li>
					))}
				</ul>
			) : (
				<p>{t("shadowExecutive.empty.historyList")}</p>
			)}
		</>
	);
}

function WeeklyReviewPanel({ review }: { review: WeeklyExecutiveReview }) {
	const { t } = useTranslation();

	if (!review.summary) {
		return <p>{t("shadowExecutive.empty.weeklyReview")}</p>;
	}

	return (
		<article className={styles.card}>
			<strong>{t("shadowExecutive.weeklyReview.title")}</strong>
			<p>{review.summary}</p>
			<p className={styles.meta}>
				{t("shadowExecutive.weeklyReview.progress", {
					percent: String(review.progressPercent),
				})}
			</p>
			<p className={styles.meta}>
				{t("shadowExecutive.weeklyReview.knowledgeGrowth", {
					value: String(review.knowledgeGrowth),
				})}
			</p>
			<p className={styles.meta}>
				{t("shadowExecutive.weeklyReview.completedMissions", {
					count: String(review.completedMissions),
				})}
			</p>
			<p className={styles.meta}>
				{t("shadowExecutive.weeklyReview.missedReviews", {
					count: String(review.missedReviews),
				})}
			</p>
			<p className={styles.meta}>
				{t("shadowExecutive.weeklyReview.learningMinutes", {
					minutes: String(review.learningMinutes),
				})}
			</p>
			{review.recommendations.length > 0 ? (
				<ul className={styles.list}>
					{review.recommendations.map((item) => (
						<li key={item} className={styles.listItem}>
							{item}
						</li>
					))}
				</ul>
			) : null}
			{review.nextWeekPlan ? (
				<p className={styles.meta}>
					<strong>{t("shadowExecutive.weeklyReview.nextWeekPlan")}</strong>
					<br />
					{review.nextWeekPlan}
				</p>
			) : null}
		</article>
	);
}

function DecisionInspector({
	explanation,
	open,
	onClose,
}: {
	explanation: DecisionExplanation | null;
	open: boolean;
	onClose: () => void;
}) {
	const { t } = useTranslation();

	if (!explanation) {
		return null;
	}

	return (
		<Dialog
			open={open}
			onClose={onClose}
			title={t("shadowExecutive.inspector.title")}
			description={explanation.title}
		>
			<div className={styles.explanationSection}>
				<h4>{t("shadowExecutive.inspector.summary")}</h4>
				<p>{explanation.summary}</p>
			</div>
			<div className={styles.explanationSection}>
				<h4>{t("shadowExecutive.inspector.reason")}</h4>
				<p>{explanation.reason.summary}</p>
				{explanation.reason.detail ? (
					<p className={styles.meta}>{explanation.reason.detail}</p>
				) : null}
			</div>
			<div className={styles.explanationSection}>
				<h4>{t("shadowExecutive.inspector.evidence")}</h4>
				{explanation.evidence.length > 0 ? (
					<ul className={styles.evidenceList}>
						{explanation.evidence.map((item) => (
							<li key={`${item.source}-${item.reference}`}>
								<strong>
									{t(`shadowExecutive.evidenceSources.${item.source}`)}
								</strong>
								: {item.label}
							</li>
						))}
					</ul>
				) : (
					<p>{t("shadowExecutive.empty.evidence")}</p>
				)}
			</div>
			<div className={styles.explanationSection}>
				<h4>{t("shadowExecutive.inspector.impacts")}</h4>
				<div className={styles.tagList}>
					{explanation.impacts.map((impact) => (
						<span key={impact} className={styles.tag}>
							{t(`shadowExecutive.impacts.${impact}`)}
						</span>
					))}
				</div>
			</div>
			{explanation.linkedGoalId ? (
				<div className={styles.explanationSection}>
					<h4>{t("shadowExecutive.inspector.goalLink")}</h4>
					<p>{explanation.linkedGoalTitle ?? explanation.linkedGoalId}</p>
				</div>
			) : null}
			{explanation.linkedConceptKey ? (
				<div className={styles.explanationSection}>
					<h4>{t("shadowExecutive.inspector.conceptLink")}</h4>
					<p>{explanation.linkedConceptKey.replace(/_/g, " ")}</p>
				</div>
			) : null}
		</Dialog>
	);
}

function PendingDecisionsPanel({
	decisions,
	onApprove,
	onReject,
	onDefer,
	onInspect,
	busyId,
}: {
	decisions: ExecutiveDecision[];
	onApprove: (id: string) => void;
	onReject: (id: string) => void;
	onDefer: (id: string) => void;
	onInspect: (decision: ExecutiveDecision) => void;
	busyId: string | null;
}) {
	const { t } = useTranslation();

	if (decisions.length === 0) {
		return <p>{t("shadowExecutive.empty.pendingDecisions")}</p>;
	}

	return (
		<ul className={styles.list}>
			{decisions.map((decision) => (
				<li key={decision.id} className={styles.listItem}>
					<div className={styles.actions}>
						<strong>{decision.title}</strong>
						<span className={styles.pendingBadge}>
							{t("shadowExecutive.pending.badge")}
						</span>
					</div>
					<p>{decision.summary}</p>
					<p className={styles.meta}>
						{t("shadowExecutive.decision.typeLabel", { value: decision.type })}{" "}
						·{" "}
						<span className={priorityClass(decision.priority)}>
							{t("shadowExecutive.priorityLabel", { value: decision.priority })}
						</span>
					</p>
					<div className={styles.actions}>
						<button
							type="button"
							disabled={busyId === decision.id}
							onClick={() => onApprove(decision.id)}
						>
							{t("shadowExecutive.actions.approve")}
						</button>
						<button
							type="button"
							disabled={busyId === decision.id}
							onClick={() => onReject(decision.id)}
						>
							{t("shadowExecutive.actions.reject")}
						</button>
						<button
							type="button"
							disabled={busyId === decision.id}
							onClick={() => onDefer(decision.id)}
						>
							{t("shadowExecutive.actions.defer")}
						</button>
						<button type="button" onClick={() => onInspect(decision)}>
							{t("shadowExecutive.actions.why")}
						</button>
					</div>
				</li>
			))}
		</ul>
	);
}

export function ExecutiveCenter() {
	const { t } = useTranslation();
	const [dashboard, setDashboard] = useState<ExecutiveDashboard | null>(null);
	const [history, setHistory] = useState<DecisionHistory | null>(null);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [busyId, setBusyId] = useState<string | null>(null);
	const [inspectorOpen, setInspectorOpen] = useState(false);
	const [inspectedDecision, setInspectedDecision] =
		useState<ExecutiveDecision | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [nextDashboard, nextHistory] = await Promise.all([
			shadowExecutiveService.getDashboard(),
			shadowExecutiveService.getHistory(),
		]);
		setDashboard(nextDashboard);
		setHistory(nextHistory);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowExecutive.errors.loadFailed"));
		});
	}, [load, t]);

	const handleDecisionAction = async (
		id: string,
		action: "approve" | "reject" | "defer",
	) => {
		setError(null);
		setMessage(null);
		setBusyId(id);

		try {
			if (action === "approve") {
				await shadowExecutiveService.approveDecision(id);
				setMessage(t("shadowExecutive.messages.approved"));
			} else if (action === "reject") {
				await shadowExecutiveService.rejectDecision(id);
				setMessage(t("shadowExecutive.messages.rejected"));
			} else {
				await shadowExecutiveService.deferDecision(id);
				setMessage(t("shadowExecutive.messages.deferred"));
			}

			await load();
		} catch {
			setError(t("shadowExecutive.errors.actionFailed"));
		} finally {
			setBusyId(null);
		}
	};

	const handleReset = async () => {
		setError(null);
		setMessage(null);

		try {
			await shadowExecutiveService.reset();
			await load();
			setMessage(t("shadowExecutive.reset.success"));
		} catch {
			setError(t("shadowExecutive.errors.resetFailed"));
		}
	};

	const handleInspect = (decision: ExecutiveDecision) => {
		setInspectedDecision(decision);
		setInspectorOpen(true);
	};

	if (!dashboard) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowExecutive}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.section}>
				<h2>{t("shadowExecutive.today.title")}</h2>
				<AgendaPanel agenda={dashboard.plan.agenda} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.upcoming.title")}</h2>
				<UpcomingPanel agenda={dashboard.plan.agenda} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.recommendations.title")}</h2>
				<RecommendationsPanel
					recommendations={dashboard.plan.recommendations}
				/>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.pending.title")}</h2>
				<PendingDecisionsPanel
					decisions={dashboard.pendingDecisions}
					onApprove={(id) => void handleDecisionAction(id, "approve")}
					onReject={(id) => void handleDecisionAction(id, "reject")}
					onDefer={(id) => void handleDecisionAction(id, "defer")}
					onInspect={handleInspect}
					busyId={busyId}
				/>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.history.title")}</h2>
				<DecisionHistoryPanel history={history} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.weeklyReview.title")}</h2>
				<WeeklyReviewPanel review={dashboard.plan.weeklyReview} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowExecutive.actions.title")}</h2>
				<div className={styles.actions}>
					<button type="button" onClick={() => void handleReset()}>
						{t("shadowExecutive.reset.action")}
					</button>
				</div>
			</section>

			<DecisionInspector
				explanation={
					inspectedDecision ? buildExplanation(inspectedDecision) : null
				}
				open={inspectorOpen}
				onClose={() => {
					setInspectorOpen(false);
					setInspectedDecision(null);
				}}
			/>
		</div>
	);
}
