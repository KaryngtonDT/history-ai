import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { contentService } from "@/services/content/ContentService";
import type { Content } from "@/services/content/domain/Content";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import styles from "./Library.module.css";

export function Library() {
	const [contents, setContents] = useState<Content[] | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		void contentService
			.listContents()
			.then((items) => {
				setContents(items);
				setLoadError(null);
			})
			.catch(() => {
				setContents([]);
				setLoadError(
					"Could not reach the server. Check that the backend is running.",
				);
			});
	}, []);

	return (
		<div className={styles.root}>
			<LibraryHeader />
			{contents === null ? (
				<div className={styles.loading}>
					<Spinner label="Loading library" />
				</div>
			) : loadError !== null ? (
				<EmptyState title="Unable to load library" description={loadError} />
			) : contents.length === 0 ? (
				<EmptyState
					title="No content yet"
					description="Import your first PDF."
				/>
			) : (
				<LibraryContentList contents={contents} />
			)}
		</div>
	);
}
