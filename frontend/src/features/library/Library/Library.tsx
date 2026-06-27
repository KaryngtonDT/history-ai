import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { AssignToCollectionDialog } from "@/features/collection/AssignToCollectionDialog";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItem } from "@/services/library/types";
import { searchService } from "@/services/search/SearchService";
import type { SearchLibraryItem } from "@/services/search/types";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import { LibrarySearchInput } from "../LibrarySearchInput";
import styles from "./Library.module.css";

export function Library() {
	const [items, setItems] = useState<LibraryItem[] | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [searchQuery, setSearchQuery] = useState("");
	const [searchResults, setSearchResults] = useState<
		SearchLibraryItem[] | null
	>(null);
	const [searchLoading, setSearchLoading] = useState(false);
	const [searchError, setSearchError] = useState<string | null>(null);
	const [assignLibraryItemId, setAssignLibraryItemId] = useState<string | null>(
		null,
	);

	const isSearching = searchQuery.trim() !== "";

	useEffect(() => {
		void libraryService
			.listItems()
			.then((libraryItems) => {
				setItems(libraryItems);
				setLoadError(null);
			})
			.catch(() => {
				setItems([]);
				setLoadError(
					"Could not reach the server. Check that the backend is running.",
				);
			});
	}, []);

	useEffect(() => {
		if (!isSearching) {
			setSearchResults(null);
			setSearchError(null);
			setSearchLoading(false);
			return;
		}

		let cancelled = false;
		setSearchLoading(true);
		setSearchError(null);

		void searchService
			.searchLibrary(searchQuery)
			.then((results) => {
				if (cancelled) {
					return;
				}

				setSearchResults(results);
				setSearchLoading(false);
			})
			.catch(() => {
				if (cancelled) {
					return;
				}

				setSearchResults([]);
				setSearchError("Could not search the library. Please try again.");
				setSearchLoading(false);
			});

		return () => {
			cancelled = true;
		};
	}, [isSearching, searchQuery]);

	return (
		<div className={styles.root}>
			<LibraryHeader />
			<LibrarySearchInput value={searchQuery} onChange={setSearchQuery} />
			{isSearching ? (
				searchLoading ? (
					<div className={styles.loading}>
						<Spinner label="Searching library" />
					</div>
				) : searchError !== null ? (
					<EmptyState
						title="Unable to search library"
						description={searchError}
					/>
				) : searchResults !== null && searchResults.length === 0 ? (
					<EmptyState
						title="No results found"
						description="Try a different search term."
					/>
				) : searchResults !== null ? (
					<LibraryContentList
						items={searchResults}
						onAssignToCollection={setAssignLibraryItemId}
					/>
				) : null
			) : items === null ? (
				<div className={styles.loading}>
					<Spinner label="Loading library" />
				</div>
			) : loadError !== null ? (
				<EmptyState title="Unable to load library" description={loadError} />
			) : items.length === 0 ? (
				<EmptyState
					title="No library items yet"
					description="Saved learning artifacts will appear here once you add them to your library."
				/>
			) : (
				<LibraryContentList
					items={items}
					onAssignToCollection={setAssignLibraryItemId}
				/>
			)}
			<AssignToCollectionDialog
				open={assignLibraryItemId !== null}
				onClose={() => setAssignLibraryItemId(null)}
				libraryItemId={assignLibraryItemId ?? ""}
			/>
		</div>
	);
}
