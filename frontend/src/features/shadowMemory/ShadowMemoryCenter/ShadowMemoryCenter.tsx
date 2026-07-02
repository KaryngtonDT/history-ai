import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowMemoryService } from "@/services/shadowMemory/ShadowMemoryService";
import type {
	KnowledgeConnection,
	KnowledgeItem,
	LearningJourney,
	MemorySearchResult,
	MemoryTimeline,
	MemoryTimelineEntry,
} from "@/services/shadowMemory/types";
import styles from "../shadowMemory.module.css";

function ProgressMeter({ percent }: { percent: number }) {
	return (
		<div className={styles.progressBar} aria-hidden="true">
			<span style={{ width: `${Math.min(100, percent)}%` }} />
		</div>
	);
}

function KnowledgeList({ items }: { items: KnowledgeItem[] }) {
	const { t } = useTranslation();

	if (items.length === 0) {
		return <p>{t("shadowMemory.empty.concepts")}</p>;
	}

	return (
		<ul className={styles.list}>
			{items.map((item) => (
				<li key={item.key} className={styles.listItem}>
					<strong>{item.label}</strong>
					<p className={styles.meta}>
						{t(`shadowMemory.progress.${item.progress}`)} ·{" "}
						{item.progressPercent}%
					</p>
					<ProgressMeter percent={item.progressPercent} />
					<p>{item.explanation}</p>
				</li>
			))}
		</ul>
	);
}

function TimelineList({ entries }: { entries: MemoryTimelineEntry[] }) {
	const { t } = useTranslation();

	if (entries.length === 0) {
		return <p>{t("shadowMemory.empty.timeline")}</p>;
	}

	return (
		<ul className={styles.list}>
			{entries.map((entry) => (
				<li key={entry.id} className={styles.listItem}>
					<strong>{entry.label}</strong>
					<p className={styles.meta}>
						{new Date(entry.recordedAt).toLocaleString()} · {entry.category}
					</p>
					<p>{entry.detail}</p>
				</li>
			))}
		</ul>
	);
}

function ConnectionList({
	connections,
}: {
	connections: KnowledgeConnection[];
}) {
	const { t } = useTranslation();

	if (connections.length === 0) {
		return <p>{t("shadowMemory.empty.connections")}</p>;
	}

	return (
		<ul className={styles.list}>
			{connections.map((connection) => (
				<li
					key={`${connection.fromKey}-${connection.toKey}`}
					className={styles.listItem}
				>
					<span className={styles.connectionArrow}>{connection.label}</span>
					<p>{connection.reason}</p>
				</li>
			))}
		</ul>
	);
}

function JourneyPanel({ journey }: { journey: LearningJourney["journey"] }) {
	const { t } = useTranslation();
	const steps = [
		{ key: "today", value: journey.today },
		{ key: "nextStep", value: journey.nextStep },
		{ key: "preparation", value: journey.preparation },
		{ key: "longTerm", value: journey.longTerm },
	] as const;

	return (
		<div className={styles.journeyGrid}>
			{steps.map((step) => (
				<div key={step.key} className={styles.journeyStep}>
					<strong>{t(`shadowMemory.journey.${step.key}`)}</strong>
					{step.value ? (
						<>
							<p>{step.value.label}</p>
							{typeof step.value.progressPercent === "number" ? (
								<ProgressMeter percent={step.value.progressPercent} />
							) : null}
						</>
					) : (
						<p className={styles.meta}>{t("shadowMemory.journey.none")}</p>
					)}
				</div>
			))}
		</div>
	);
}

function SearchResults({ results }: { results: MemorySearchResult | null }) {
	const { t } = useTranslation();

	if (!results) {
		return null;
	}

	if (results.total === 0) {
		return <p>{t("shadowMemory.search.noResults")}</p>;
	}

	return (
		<div className={styles.section}>
			<p className={styles.meta}>
				{t("shadowMemory.search.total", { count: String(results.total) })}
			</p>
			{results.concepts.length > 0 ? (
				<KnowledgeList items={results.concepts} />
			) : null}
		</div>
	);
}

export function ShadowMemoryCenter() {
	const { t } = useTranslation();
	const [timeline, setTimeline] = useState<MemoryTimeline | null>(null);
	const [journey, setJourney] = useState<LearningJourney | null>(null);
	const [connections, setConnections] = useState<KnowledgeConnection[]>([]);
	const [query, setQuery] = useState("");
	const [searchResults, setSearchResults] = useState<MemorySearchResult | null>(
		null,
	);
	const [message, setMessage] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	const load = useCallback(async () => {
		setError(null);
		const [nextTimeline, nextJourney, nextConnections] = await Promise.all([
			shadowMemoryService.getTimeline(),
			shadowMemoryService.getJourney(),
			shadowMemoryService.getConnections(),
		]);
		setTimeline(nextTimeline);
		setJourney(nextJourney);
		setConnections(nextConnections.connections);
	}, []);

	useEffect(() => {
		void load().catch(() => {
			setError(t("shadowMemory.errors.loadFailed"));
		});
	}, [load, t]);

	const handleSearch = async () => {
		setError(null);
		setMessage(null);

		try {
			const results = await shadowMemoryService.search({ query });
			setSearchResults(results);
		} catch {
			setError(t("shadowMemory.errors.searchFailed"));
		}
	};

	const handleReset = async () => {
		setError(null);
		setMessage(null);

		try {
			const nextTimeline = await shadowMemoryService.reset();
			setTimeline(nextTimeline);
			setSearchResults(null);
			setMessage(t("shadowMemory.reset.success"));
			await load();
		} catch {
			setError(t("shadowMemory.errors.resetFailed"));
		}
	};

	if (!timeline) {
		return <p>{t("common.loading")}</p>;
	}

	return (
		<div className={styles.shadowMemory}>
			{error ? <p role="alert">{error}</p> : null}
			{message ? <p>{message}</p> : null}

			<section className={styles.section}>
				<h2>{t("shadowMemory.journey.title")}</h2>
				{journey ? <JourneyPanel journey={journey.journey} /> : null}
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMemory.concepts.title")}</h2>
				<KnowledgeList items={timeline.concepts} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMemory.connections.title")}</h2>
				<ConnectionList connections={connections} />
			</section>

			<section className={styles.section}>
				<h2>{t("shadowMemory.timeline.title")}</h2>
				<TimelineList entries={timeline.timeline} />
			</section>

			<section className={`${styles.section} ${styles.searchBox}`}>
				<h2>{t("shadowMemory.search.title")}</h2>
				<input
					type="search"
					value={query}
					onChange={(event) => setQuery(event.target.value)}
					placeholder={t("shadowMemory.search.placeholder")}
				/>
				<div className={styles.actions}>
					<button type="button" onClick={() => void handleSearch()}>
						{t("common.search")}
					</button>
					<button type="button" onClick={() => void handleReset()}>
						{t("shadowMemory.reset.action")}
					</button>
				</div>
				<SearchResults results={searchResults} />
			</section>
		</div>
	);
}
