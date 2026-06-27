import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItem } from "@/services/library/types";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import styles from "./Library.module.css";

export function Library() {
	const [items, setItems] = useState<LibraryItem[] | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);

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

	return (
		<div className={styles.root}>
			<LibraryHeader />
			{items === null ? (
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
				<LibraryContentList items={items} />
			)}
		</div>
	);
}
