import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowKnowledgeService } from "@/services/shadowKnowledge/ShadowKnowledgeService";
import type {
	KnowledgeGap,
	KnowledgeGraph,
	KnowledgeNode,
	KnowledgePath,
	KnowledgeRadar,
	KnowledgeSearchResult,
} from "@/services/shadowKnowledge/types";
import styles from "../shadowKnowledge.module.css";

function ProgressBar({ value }: { value: number }) {
	return (
		<div className={styles.progressBar} aria-hidden="true">
			<span style={{ width: `${Math.min(100, value)}%` }} />
		</div>
	);
}

function masteryFor(graph: KnowledgeGraph, key: string) {
	return graph.masteries.find((item) => item.nodeKey === key);
}

function NodeCard({
	node,
	graph,
	onSelect,
}: {
	node: KnowledgeNode;
	graph: KnowledgeGraph;
	onSelect: (key: string) => void;
}) {
	const { t } = useTranslation();
	const mastery = masteryFor(graph, node.key);

	return (
		<article className={styles.card}>
			<strong>{node.label}</strong>
			<p className={styles.meta}>
				{node.type} · {node.sources.join(", ")}
			</p>
			<p>{node.explanation}</p>
			<p className={styles.meta}>
				{t("shadowKnowledge.masteryLabel", {
					percent: String(mastery?.percent ?? 0),
				})}
			</p>
			<ProgressBar value={mastery?.percent ?? 0} />
			<div className={styles.actions}>
				<button type="button" onClick={() => onSelect(node.key)}>
					{t("shadowKnowledge.actions.inspect")}
				</button>
			</div>
		</article>
	);
}

function PathCard({ path }: { path: KnowledgePath }) {
	return (
		<article className={styles.card}>
			<strong>{path.label}</strong>
			<div className={styles.pathSteps}>
				{path.steps.map((step, index) => (
					<span key={step.key}>
						{index > 0 ? (
							<span className={styles.pathArrow}> → </span>
						) : null}
						<span className={styles.pathStep}>{step.label}</span>
					</span>
				))}
			</div>
		</article>
	);
}

function GapList({ gaps }: { gaps: KnowledgeGap[] }) {
	const { t } = useTranslation();

	if (gaps.length === 0) {
		return <p>{t("shadowKnowledge.empty.gaps")}</p>;
	}

	return (
		<ul className={styles.list}>
			{gaps.map((gap) => (
				<li key={gap.conceptKey} className={styles.listItem}>
					<strong>{gap.label}</strong>
					<p>{gap.recommended}</p>
					<p className={styles.meta}>{gap.reason}</p>
					<ProgressBar value={gap.masteryPercent} />
				</li>
			))}
		</ul>
	);
}

export function ShadowKnowledgeCenter() {
	const { t } = useTranslation();
	const [graph, setGraph] = useState<KnowledgeGraph | null>(null);
	const [radar, setRadar] = useState<KnowledgeRadar | null>(null);
	const [selectedKey, setSelectedKey] = useState<string | null>(null);
	const [selectedDetail, setSelectedDetail] = useState<string | null>(null);
	const [searchQuery, setSearchQuery] = useState("");
	const [searchResult, setSearchResult] = useState<KnowledgeSearchResult | null>(
		null,
	);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [nextGraph, gapsResponse] = await Promise.all([
			shadowKnowledgeService.getGraph(),
			shadowKnowledgeService.getGaps("kubernetes"),
		]);
		setGraph(nextGraph);
		setRadar(gapsResponse.radar);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowKnowledge.errors.loadFailed"));
		});
	}, [load, t]);

	useEffect(() => {
		if (!selectedKey) {
			setSelectedDetail(null);
			return;
		}

		void shadowKnowledgeService
			.getNode(selectedKey)
			.then((detail) => {
				setSelectedDetail(detail.node.label);
			})
			.catch(() => {
				setError(t("shadowKnowledge.errors.nodeFailed"));
			});
	}, [selectedKey, t]);

	const handleSearch = async () => {
		if (!searchQuery.trim()) {
			return;
		}

		setError(null);
		setMessage(null);

		try {
			const result = await shadowKnowledgeService.search({
				query: searchQuery.trim(),
			});
			setSearchResult(result);
		} catch {
			setError(t("shadowKnowledge.errors.searchFailed"));
		}
	};

	const handleRebuild = async () => {
		setError(null);
		setMessage(null);

		try {
			const nextGraph = await shadowKnowledgeService.rebuild();
			setGraph(nextGraph);
			const gapsResponse = await shadowKnowledgeService.getGaps("kubernetes");
			setRadar(gapsResponse.radar);
			setMessage(t("shadowKnowledge.rebuild.success"));
		} catch {
			setError(t("shadowKnowledge.errors.rebuildFailed"));
		}
	};

	const handleReset = async () => {
		setError(null);
		setMessage(null);

		try {
			const nextGraph = await shadowKnowledgeService.reset();
			setGraph(nextGraph);
			const gapsResponse = await shadowKnowledgeService.getGaps("kubernetes");
			setRadar(gapsResponse.radar);
			setSelectedKey(null);
			setSearchResult(null);
			setMessage(t("shadowKnowledge.reset.success"));
		} catch {
			setError(t("shadowKnowledge.errors.resetFailed"));
		}
	};

	if (!graph) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowKnowledge}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.overview.title")}</h2>
				<div className={styles.cardGrid}>
					<article className={styles.card}>
						<strong>{t("shadowKnowledge.overview.nodes")}</strong>
						<p>{graph.nodes.length}</p>
					</article>
					<article className={styles.card}>
						<strong>{t("shadowKnowledge.overview.edges")}</strong>
						<p>{graph.edges.length}</p>
					</article>
					<article className={styles.card}>
						<strong>{t("shadowKnowledge.overview.readiness")}</strong>
						<p>{radar?.readinessPercent ?? 0}%</p>
						<ProgressBar value={radar?.readinessPercent ?? 0} />
					</article>
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.search.title")}</h2>
				<div className={styles.searchRow}>
					<input
						type="search"
						value={searchQuery}
						onChange={(event) => setSearchQuery(event.target.value)}
						placeholder={t("shadowKnowledge.search.placeholder")}
					/>
					<button type="button" onClick={() => void handleSearch()}>
						{t("common.search")}
					</button>
				</div>
				{searchResult ? (
					<p className={styles.meta}>
						{t("shadowKnowledge.search.results", {
							count: String(searchResult.total),
						})}
					</p>
				) : null}
			</section>

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.graph.title")}</h2>
				<div className={styles.cardGrid}>
					{graph.nodes.map((node) => (
						<NodeCard
							key={node.key}
							node={node}
							graph={graph}
							onSelect={setSelectedKey}
						/>
					))}
				</div>
				{selectedDetail ? (
					<p className={styles.meta}>
						{t("shadowKnowledge.selected", { label: selectedDetail })}
					</p>
				) : null}
			</section>

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.paths.title")}</h2>
				<div className={styles.cardGrid}>
					{graph.paths.map((path) => (
						<PathCard key={path.key} path={path} />
					))}
				</div>
			</section>

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.gaps.title")}</h2>
				<p className={styles.meta}>
					{t("shadowKnowledge.gaps.goal", {
						label: radar?.goalLabel ?? t("shadowKnowledge.gaps.defaultGoal"),
					})}
				</p>
				<GapList gaps={radar?.gaps ?? []} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowKnowledge.actions.title")}</h2>
				<div className={styles.actions}>
					<button type="button" onClick={() => void handleRebuild()}>
						{t("shadowKnowledge.rebuild.action")}
					</button>
					<button type="button" onClick={() => void handleReset()}>
						{t("shadowKnowledge.reset.action")}
					</button>
				</div>
			</section>
		</div>
	);
}
