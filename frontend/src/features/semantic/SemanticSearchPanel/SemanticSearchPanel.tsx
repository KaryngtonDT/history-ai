import { type FormEvent, useMemo, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import type { Artifact } from "@/services/artifact/types";
import { semanticSearchService } from "@/services/semantic/SemanticSearchService";
import type { RetrievedChunk } from "@/services/semantic/types";
import { SemanticSearchResults } from "../SemanticSearchResults";
import styles from "./SemanticSearchPanel.module.css";

const MIN_QUERY_LENGTH = 2;

interface SemanticSearchPanelProps {
	contentId: string;
	artifacts: Artifact[];
}

type SemanticSearchViewState =
	| { status: "idle" }
	| { status: "loading" }
	| { status: "ready"; results: RetrievedChunk[] }
	| { status: "empty" }
	| { status: "error" };

export function SemanticSearchPanel({
	contentId,
	artifacts,
}: SemanticSearchPanelProps) {
	const [query, setQuery] = useState("");
	const [viewState, setViewState] = useState<SemanticSearchViewState>({
		status: "idle",
	});

	const artifactTypesById = useMemo(
		() =>
			Object.fromEntries(
				artifacts.map((artifact) => [artifact.id, artifact.type]),
			),
		[artifacts],
	);

	const trimmedQuery = query.trim();
	const canSearch = trimmedQuery.length >= MIN_QUERY_LENGTH;

	async function runSearch(): Promise<void> {
		if (!canSearch) {
			return;
		}

		setViewState({ status: "loading" });

		try {
			const results = await semanticSearchService.searchSemanticChunks(
				contentId,
				trimmedQuery,
			);

			if (results.length === 0) {
				setViewState({ status: "empty" });
				return;
			}

			setViewState({ status: "ready", results });
		} catch {
			setViewState({ status: "error" });
		}
	}

	function handleSubmit(event: FormEvent<HTMLFormElement>): void {
		event.preventDefault();
		void runSearch();
	}

	return (
		<Card className={styles.semanticSearchPanel}>
			<p className={styles.label}>Semantic Search</p>
			<form className={styles.searchForm} onSubmit={handleSubmit}>
				<label className={styles.searchField}>
					<span className={styles.searchLabel}>Search query</span>
					<input
						className={styles.searchInput}
						type="search"
						value={query}
						onChange={(event) => setQuery(event.target.value)}
						onKeyDown={(event) => {
							if (event.key === "Enter") {
								event.preventDefault();
								void runSearch();
							}
						}}
						placeholder="Search artifact chunks"
						minLength={MIN_QUERY_LENGTH}
						aria-describedby="semantic-search-hint"
					/>
				</label>
				<Button
					type="submit"
					disabled={!canSearch || viewState.status === "loading"}
				>
					Search
				</Button>
			</form>
			<p id="semantic-search-hint" className={styles.searchHint}>
				Enter at least {MIN_QUERY_LENGTH} characters, then press Search or
				Enter.
			</p>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Searching semantic chunks" />
				</div>
			) : null}
			{viewState.status === "empty" ? (
				<EmptyState
					className={styles.emptyState}
					title="No matches found"
					description="Try a different query to search across this content's artifact chunks."
				/>
			) : null}
			{viewState.status === "error" ? (
				<EmptyState
					className={styles.emptyState}
					title="Unable to search"
					description="Something went wrong while searching semantic chunks for this content."
				/>
			) : null}
			{viewState.status === "ready" ? (
				<SemanticSearchResults
					results={viewState.results}
					artifactTypesById={artifactTypesById}
				/>
			) : null}
		</Card>
	);
}
