import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowBrainService } from "@/services/shadowBrain/ShadowBrainService";
import type {
	BrainDashboard,
	ConceptDetail,
	KnowledgeBookmark,
	KnowledgeInsight,
	KnowledgeNote,
	KnowledgeRevision,
	KnowledgeSearchHit,
	KnowledgeTimelineEvent,
	KnowledgeTreeNode,
} from "@/services/shadowBrain/types";
import styles from "../shadowBrain.module.css";

type BottomView = "notes" | "bookmarks" | "insights" | "stats";

function TreeNodes({
	nodes,
	depth,
	selectedKey,
	onSelect,
}: {
	nodes: KnowledgeTreeNode[];
	depth: number;
	selectedKey: string | null;
	onSelect: (node: KnowledgeTreeNode) => void;
}) {
	return (
		<ul className={depth === 0 ? styles.treeList : styles.treeChildren}>
			{nodes.map((node) => {
				const isActive =
					node.conceptKey !== null && node.conceptKey === selectedKey;

				return (
					<li key={node.id} className={styles.treeItem}>
						<button
							type="button"
							className={
								isActive
									? `${styles.treeButton} ${styles.treeButtonActive}`
									: styles.treeButton
							}
							onClick={() => onSelect(node)}
						>
							<span>{node.label}</span>
							{node.entryCount > 0 ? (
								<span className={styles.treeCount}>{node.entryCount}</span>
							) : null}
						</button>
						{node.children.length > 0 ? (
							<TreeNodes
								nodes={node.children}
								depth={depth + 1}
								selectedKey={selectedKey}
								onSelect={onSelect}
							/>
						) : null}
					</li>
				);
			})}
		</ul>
	);
}

function ConceptDetailPanel({
	detail,
	loading,
}: {
	detail: ConceptDetail | null;
	loading: boolean;
}) {
	const { t } = useTranslation();

	if (loading) {
		return <p className={styles.meta}>{t("common.loading")}</p>;
	}

	if (!detail) {
		return <p className={styles.meta}>{t("shadowBrain.empty.concept")}</p>;
	}

	const { entry, sources, related, notes, evolution } = detail;

	return (
		<>
			<div>
				<h3 className={styles.panelTitle}>{entry.label}</h3>
				<p className={styles.meta}>{entry.summary}</p>
				<p className={styles.meta}>
					{t("shadowBrain.concept.mastery", {
						percent: String(entry.masteryPercent),
					})}
				</p>
				<div className={styles.masteryBar} aria-hidden="true">
					<span
						className={styles.masteryFill}
						style={{ width: `${entry.masteryPercent}%` }}
					/>
				</div>
			</div>

			<div className={styles.section}>
				<h4>{t("shadowBrain.concept.evolution")}</h4>
				<div className={styles.evolutionGrid}>
					<div className={styles.evolutionStat}>
						<span className={styles.meta}>
							{t("shadowBrain.evolution.firstSeen")}
						</span>
						<span className={styles.evolutionValue}>
							{new Date(evolution.firstSeenAt).toLocaleDateString()}
						</span>
					</div>
					<div className={styles.evolutionStat}>
						<span className={styles.meta}>
							{t("shadowBrain.evolution.explanations")}
						</span>
						<span className={styles.evolutionValue}>
							{evolution.explanationCount}
						</span>
					</div>
					<div className={styles.evolutionStat}>
						<span className={styles.meta}>
							{t("shadowBrain.evolution.videos")}
						</span>
						<span className={styles.evolutionValue}>
							{evolution.videoCount}
						</span>
					</div>
					<div className={styles.evolutionStat}>
						<span className={styles.meta}>
							{t("shadowBrain.evolution.exercises")}
						</span>
						<span className={styles.evolutionValue}>
							{evolution.exerciseCount}
						</span>
					</div>
					<div className={styles.evolutionStat}>
						<span className={styles.meta}>
							{t("shadowBrain.evolution.lastRevision")}
						</span>
						<span className={styles.evolutionValue}>
							{evolution.lastRevisionAt
								? new Date(evolution.lastRevisionAt).toLocaleDateString()
								: t("shadowBrain.empty.lastRevision")}
						</span>
					</div>
				</div>
			</div>

			<div className={styles.section}>
				<h4>{t("shadowBrain.concept.sources")}</h4>
				{sources.length === 0 ? (
					<p className={styles.meta}>{t("shadowBrain.empty.sources")}</p>
				) : (
					<ul className={styles.sourceList}>
						{sources.map((source) => (
							<li key={source.id} className={styles.card}>
								<strong>
									{t(`shadowBrain.sourceTypes.${source.type}`)} · {source.label}
								</strong>
								<p className={styles.meta}>{source.resourceLabel}</p>
								{source.detail ? (
									<p className={styles.meta}>{source.detail}</p>
								) : null}
							</li>
						))}
					</ul>
				)}
			</div>

			<div className={styles.section}>
				<h4>{t("shadowBrain.concept.related")}</h4>
				{related.length === 0 ? (
					<p className={styles.meta}>{t("shadowBrain.empty.related")}</p>
				) : (
					<ul className={styles.relatedList}>
						{related.map((item) => (
							<li key={item.id} className={styles.card}>
								<strong>{item.label}</strong>
								<p className={styles.meta}>
									{t("shadowBrain.concept.mastery", {
										percent: String(item.masteryPercent),
									})}
								</p>
							</li>
						))}
					</ul>
				)}
			</div>

			{notes.length > 0 ? (
				<div className={styles.section}>
					<h4>{t("shadowBrain.concept.notes")}</h4>
					<ul className={styles.noteList}>
						{notes.map((note) => (
							<li key={note.id} className={styles.card}>
								<p>{note.body}</p>
								<p className={styles.meta}>
									{new Date(note.createdAt).toLocaleString()}
								</p>
							</li>
						))}
					</ul>
				</div>
			) : null}

			{entry.recommendations.length > 0 ? (
				<div className={styles.section}>
					<h4>{t("shadowBrain.concept.recommendations")}</h4>
					<ul className={styles.noteList}>
						{entry.recommendations.map((recommendation) => (
							<li key={recommendation} className={styles.card}>
								<p>{recommendation}</p>
							</li>
						))}
					</ul>
				</div>
			) : null}
		</>
	);
}

function HeatmapPanel({
	entries,
}: {
	entries: BrainDashboard["workspace"]["statistics"]["domainHeatmap"];
}) {
	const { t } = useTranslation();

	if (entries.length === 0) {
		return <p className={styles.meta}>{t("shadowBrain.empty.heatmap")}</p>;
	}

	return (
		<div className={styles.heatmap}>
			{entries.map((entry) => (
				<div key={entry.key} className={styles.heatmapRow}>
					<span>{entry.label}</span>
					<div className={styles.heatmapTrack} aria-hidden="true">
						<span
							className={styles.heatmapFill}
							style={{ width: `${entry.percent}%` }}
						/>
					</div>
					<span>{entry.percent}%</span>
				</div>
			))}
		</div>
	);
}

function NotesPanel({ notes }: { notes: KnowledgeNote[] }) {
	const { t } = useTranslation();

	if (notes.length === 0) {
		return <p className={styles.meta}>{t("shadowBrain.empty.notes")}</p>;
	}

	return (
		<ul className={styles.noteList}>
			{notes.map((note) => (
				<li key={note.id} className={styles.card}>
					<p>{note.body}</p>
					<p className={styles.meta}>
						{note.conceptKey ?? t("shadowBrain.notes.general")} ·{" "}
						{new Date(note.createdAt).toLocaleString()}
					</p>
				</li>
			))}
		</ul>
	);
}

function BookmarksPanel({ bookmarks }: { bookmarks: KnowledgeBookmark[] }) {
	const { t } = useTranslation();

	if (bookmarks.length === 0) {
		return <p className={styles.meta}>{t("shadowBrain.empty.bookmarks")}</p>;
	}

	return (
		<ul className={styles.bookmarkList}>
			{bookmarks.map((bookmark) => (
				<li key={bookmark.id} className={styles.card}>
					<strong>{bookmark.label}</strong>
					<p className={styles.meta}>
						{bookmark.conceptKey ??
							(bookmark.resourceType
								? t(`shadowBrain.sourceTypes.${bookmark.resourceType}`)
								: t("shadowBrain.bookmarks.resource"))}
					</p>
					{bookmark.tags.length > 0 ? (
						<div className={styles.tagList}>
							{bookmark.tags.map((tag) => (
								<span key={tag} className={styles.tag}>
									{tag}
								</span>
							))}
						</div>
					) : null}
				</li>
			))}
		</ul>
	);
}

function InsightsPanel({
	insights,
	revisions,
}: {
	insights: KnowledgeInsight[];
	revisions: KnowledgeRevision[];
}) {
	const { t } = useTranslation();

	return (
		<>
			{insights.length > 0 ? (
				<ul className={styles.insightList}>
					{insights.map((insight) => (
						<li key={insight.id} className={styles.card}>
							<strong>{insight.label}</strong>
							<p className={styles.meta}>{insight.detail}</p>
						</li>
					))}
				</ul>
			) : (
				<p className={styles.meta}>{t("shadowBrain.empty.insights")}</p>
			)}
			{revisions.length > 0 ? (
				<div className={styles.section}>
					<h4>{t("shadowBrain.revisions.title")}</h4>
					<ul className={styles.revisionList}>
						{revisions.map((revision) => (
							<li key={revision.conceptKey} className={styles.card}>
								<strong>{revision.conceptKey.replace(/_/g, " ")}</strong>
								<p className={styles.meta}>{revision.reason}</p>
								<p className={styles.meta}>
									{t("shadowBrain.revisions.due", {
										date: new Date(revision.dueAt).toLocaleDateString(),
									})}
								</p>
							</li>
						))}
					</ul>
				</div>
			) : null}
		</>
	);
}

function StatsPanel({ dashboard }: { dashboard: BrainDashboard }) {
	const { t } = useTranslation();
	const stats = dashboard.workspace.statistics;

	return (
		<>
			<div className={styles.statsGrid}>
				<div className={styles.statCard}>
					<span className={styles.meta}>{t("shadowBrain.stats.concepts")}</span>
					<span className={styles.statValue}>{stats.conceptCount}</span>
				</div>
				<div className={styles.statCard}>
					<span className={styles.meta}>{t("shadowBrain.stats.videos")}</span>
					<span className={styles.statValue}>{stats.videoCount}</span>
				</div>
				<div className={styles.statCard}>
					<span className={styles.meta}>{t("shadowBrain.stats.pdfs")}</span>
					<span className={styles.statValue}>{stats.pdfCount}</span>
				</div>
				<div className={styles.statCard}>
					<span className={styles.meta}>
						{t("shadowBrain.stats.conversations")}
					</span>
					<span className={styles.statValue}>{stats.conversationCount}</span>
				</div>
				<div className={styles.statCard}>
					<span className={styles.meta}>
						{t("shadowBrain.stats.exercises")}
					</span>
					<span className={styles.statValue}>{stats.exerciseCount}</span>
				</div>
				<div className={styles.statCard}>
					<span className={styles.meta}>{t("shadowBrain.stats.missions")}</span>
					<span className={styles.statValue}>{stats.missionCount}</span>
				</div>
			</div>
			<div className={styles.section}>
				<h4>{t("shadowBrain.stats.heatmap")}</h4>
				<HeatmapPanel entries={stats.domainHeatmap} />
			</div>
		</>
	);
}

function TimelinePanel({ events }: { events: KnowledgeTimelineEvent[] }) {
	const { t } = useTranslation();

	if (events.length === 0) {
		return <p className={styles.meta}>{t("shadowBrain.empty.timeline")}</p>;
	}

	return (
		<ul className={styles.timelineList}>
			{events.map((event) => (
				<li key={event.id} className={styles.card}>
					<strong>{event.label}</strong>
					<p className={styles.meta}>
						{new Date(event.occurredAt).toLocaleDateString()}
						{event.sourceType
							? ` · ${t(`shadowBrain.sourceTypes.${event.sourceType}`)}`
							: ""}
					</p>
				</li>
			))}
		</ul>
	);
}

function SearchResultsPanel({ hits }: { hits: KnowledgeSearchHit[] }) {
	const { t } = useTranslation();

	if (hits.length === 0) {
		return <p className={styles.meta}>{t("shadowBrain.empty.search")}</p>;
	}

	return (
		<ul className={styles.searchResults}>
			{hits.map((hit) => (
				<li key={hit.conceptKey} className={styles.card}>
					<strong>{hit.label}</strong>
					<p className={styles.meta}>{hit.summary}</p>
					<p className={styles.meta}>
						{t("shadowBrain.search.hitMeta", {
							mastery: String(hit.masteryPercent),
							sources: String(hit.sourceCount),
						})}
					</p>
				</li>
			))}
		</ul>
	);
}

export function SecondBrainCenter() {
	const { t } = useTranslation();
	const [dashboard, setDashboard] = useState<BrainDashboard | null>(null);
	const [tree, setTree] = useState<KnowledgeTreeNode[]>([]);
	const [selectedConceptKey, setSelectedConceptKey] = useState<string | null>(
		"docker",
	);
	const [conceptDetail, setConceptDetail] = useState<ConceptDetail | null>(
		null,
	);
	const [detailLoading, setDetailLoading] = useState(false);
	const [searchQuery, setSearchQuery] = useState("");
	const [searchHits, setSearchHits] = useState<KnowledgeSearchHit[]>([]);
	const [showTimeline, setShowTimeline] = useState(false);
	const [showSearchResults, setShowSearchResults] = useState(false);
	const [bottomView, setBottomView] = useState<BottomView>("notes");
	const [error, setError] = useState<string | null>(null);
	const [message, setMessage] = useState<string | null>(null);

	const loadConcept = useCallback(
		async (conceptKey: string) => {
			setDetailLoading(true);
			setError(null);

			try {
				const detail = await shadowBrainService.getConcept(conceptKey);
				setConceptDetail(detail);
				setSelectedConceptKey(conceptKey);
				setShowSearchResults(false);
				setShowTimeline(false);
			} catch {
				setError(t("shadowBrain.errors.loadConceptFailed"));
			} finally {
				setDetailLoading(false);
			}
		},
		[t],
	);

	const load = useCallback(async () => {
		setError(null);

		try {
			const [nextDashboard, treeResponse] = await Promise.all([
				shadowBrainService.getDashboard(),
				shadowBrainService.getConceptTree(),
			]);

			setDashboard(nextDashboard);
			setTree(treeResponse.tree);
			await loadConcept("docker");
		} catch {
			setError(t("shadowBrain.errors.loadFailed"));
		}
	}, [loadConcept, t]);

	useEffect(() => {
		void load();
	}, [load]);

	const handleTreeSelect = (node: KnowledgeTreeNode) => {
		if (node.conceptKey) {
			void loadConcept(node.conceptKey);
		}
	};

	const handleSearch = async (event: React.FormEvent) => {
		event.preventDefault();

		if (searchQuery.trim() === "") {
			return;
		}

		setError(null);

		try {
			const response = await shadowBrainService.search(searchQuery.trim());
			setSearchHits(response.hits);
			setShowSearchResults(true);
			setShowTimeline(false);
		} catch {
			setError(t("shadowBrain.errors.searchFailed"));
		}
	};

	const handleTimelineToggle = async () => {
		if (showTimeline) {
			setShowTimeline(false);
			return;
		}

		setError(null);

		try {
			const response = await shadowBrainService.getTimeline();
			setDashboard((current) =>
				current
					? {
							...current,
							workspace: {
								...current.workspace,
								timeline: response.events,
							},
						}
					: current,
			);
			setShowTimeline(true);
			setShowSearchResults(false);
		} catch {
			setError(t("shadowBrain.errors.timelineFailed"));
		}
	};

	const handleRebuild = async () => {
		setError(null);
		setMessage(null);

		try {
			const next = await shadowBrainService.rebuild();
			setDashboard(next);
			setMessage(t("shadowBrain.messages.rebuilt"));
		} catch {
			setError(t("shadowBrain.errors.rebuildFailed"));
		}
	};

	return (
		<div className={styles.shadowBrain}>
			<div className={styles.toolbar}>
				<form
					className={styles.searchForm}
					onSubmit={(event) => void handleSearch(event)}
				>
					<input
						className={styles.searchInput}
						type="search"
						value={searchQuery}
						onChange={(event) => setSearchQuery(event.target.value)}
						placeholder={t("shadowBrain.search.placeholder")}
						aria-label={t("shadowBrain.search.label")}
					/>
					<button type="submit" className={styles.toolbarButton}>
						{t("common.search")}
					</button>
				</form>
				<div className={styles.toolbarActions}>
					<button
						type="button"
						className={
							showTimeline
								? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
								: styles.toolbarButton
						}
						onClick={() => void handleTimelineToggle()}
					>
						{t("shadowBrain.toolbar.timeline")}
					</button>
					<button
						type="button"
						className={
							bottomView === "stats"
								? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
								: styles.toolbarButton
						}
						onClick={() => {
							setBottomView("stats");
							setShowTimeline(false);
							setShowSearchResults(false);
						}}
					>
						{t("shadowBrain.toolbar.statistics")}
					</button>
					<button
						type="button"
						className={styles.toolbarButton}
						onClick={() => void handleRebuild()}
					>
						{t("shadowBrain.toolbar.rebuild")}
					</button>
				</div>
			</div>

			{error ? (
				<p className={`${styles.message} ${styles.error}`} role="alert">
					{error}
				</p>
			) : null}
			{message ? (
				<p className={`${styles.message} ${styles.success}`}>{message}</p>
			) : null}

			<div className={styles.mainGrid}>
				<section className={styles.explorerPanel}>
					<h3 className={styles.panelTitle}>
						{t("shadowBrain.explorer.title")}
					</h3>
					<TreeNodes
						nodes={tree}
						depth={0}
						selectedKey={selectedConceptKey}
						onSelect={handleTreeSelect}
					/>
				</section>

				<section className={styles.detailPanel}>
					<h3 className={styles.panelTitle}>
						{showTimeline
							? t("shadowBrain.timeline.title")
							: showSearchResults
								? t("shadowBrain.search.title")
								: t("shadowBrain.concept.title")}
					</h3>
					{showTimeline && dashboard ? (
						<TimelinePanel events={dashboard.workspace.timeline} />
					) : showSearchResults ? (
						<SearchResultsPanel hits={searchHits} />
					) : (
						<ConceptDetailPanel
							detail={conceptDetail}
							loading={detailLoading}
						/>
					)}
				</section>
			</div>

			<div className={styles.bottomStrip}>
				<section className={styles.bottomPanel}>
					<div className={styles.toolbarActions}>
						<button
							type="button"
							className={
								bottomView === "notes"
									? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
									: styles.toolbarButton
							}
							onClick={() => setBottomView("notes")}
						>
							{t("shadowBrain.bottom.notes")}
						</button>
						<button
							type="button"
							className={
								bottomView === "bookmarks"
									? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
									: styles.toolbarButton
							}
							onClick={() => setBottomView("bookmarks")}
						>
							{t("shadowBrain.bottom.bookmarks")}
						</button>
						<button
							type="button"
							className={
								bottomView === "insights"
									? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
									: styles.toolbarButton
							}
							onClick={() => setBottomView("insights")}
						>
							{t("shadowBrain.bottom.insights")}
						</button>
						<button
							type="button"
							className={
								bottomView === "stats"
									? `${styles.toolbarButton} ${styles.toolbarButtonActive}`
									: styles.toolbarButton
							}
							onClick={() => setBottomView("stats")}
						>
							{t("shadowBrain.bottom.stats")}
						</button>
					</div>
					{bottomView === "notes" && dashboard ? (
						<NotesPanel notes={dashboard.workspace.notes} />
					) : null}
					{bottomView === "bookmarks" && dashboard ? (
						<BookmarksPanel bookmarks={dashboard.workspace.bookmarks} />
					) : null}
					{bottomView === "insights" && dashboard ? (
						<InsightsPanel
							insights={dashboard.insights}
							revisions={dashboard.revisions}
						/>
					) : null}
					{bottomView === "stats" && dashboard ? (
						<StatsPanel dashboard={dashboard} />
					) : null}
				</section>
			</div>
		</div>
	);
}
