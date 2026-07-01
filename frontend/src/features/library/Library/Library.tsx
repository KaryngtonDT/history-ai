import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { AssignToCollectionDialog } from "@/features/collection/AssignToCollectionDialog";
import { useTranslation } from "@/i18n";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItem } from "@/services/library/types";
import { searchService } from "@/services/search/SearchService";
import type { SearchLibraryItem } from "@/services/search/types";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import { LibrarySearchInput } from "../LibrarySearchInput";
import styles from "./Library.module.css";

export function Library() {
	const { t } = useTranslation();
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
				setLoadError(t("workspace.page.backendUnavailable"));
			});
	}, [t]);

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
				setSearchError(t("workspace.library.unableToSearchLibrary"));
				setSearchLoading(false);
			});

		return () => {
			cancelled = true;
		};
	}, [isSearching, searchQuery, t]);

	return (
		<div className={styles.root}>
			<LibraryHeader />
			<LibrarySearchInput value={searchQuery} onChange={setSearchQuery} />
			{isSearching ? (
				searchLoading ? (
					<div className={styles.loading}>
						<Spinner label={t("workspace.library.searchingLibrary")} />
					</div>
				) : searchError !== null ? (
					<EmptyState
						title={t("workspace.library.unableToSearchLibrary")}
						description={searchError}
					/>
				) : searchResults !== null && searchResults.length === 0 ? (
					<EmptyState
						title={t("workspace.library.noResultsFound")}
						description={t("workspace.library.tryDifferentSearchTerm")}
					/>
				) : searchResults !== null ? (
					<LibraryContentList
						items={searchResults}
						onAssignToCollection={setAssignLibraryItemId}
					/>
				) : null
			) : items === null ? (
				<div className={styles.loading}>
					<Spinner label={t("workspace.library.loadingLibrary")} />
				</div>
			) : loadError !== null ? (
				<EmptyState
					title={t("workspace.library.unableToLoadLibrary")}
					description={loadError}
				/>
			) : items.length === 0 ? (
				<EmptyState
					title={t("workspace.library.noLibraryItemsYet")}
					description={t("workspace.library.noLibraryItemsDescription")}
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
