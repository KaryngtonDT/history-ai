import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { contentService } from "@/services/content/ContentService";
import type { Content } from "@/services/content/types";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import styles from "./Library.module.css";

export function Library() {
	const [contents, setContents] = useState<Content[] | null>(null);

	useEffect(() => {
		void contentService.listContents().then(setContents);
	}, []);

	return (
		<div className={styles.root}>
			<LibraryHeader />
			{contents === null ? (
				<div className={styles.loading}>
					<Spinner label="Loading library" />
				</div>
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
