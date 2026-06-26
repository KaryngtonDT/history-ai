import { EmptyState } from "@/components/ui/EmptyState";
import { libraryService } from "@/services/library/LibraryService";
import { LibraryContentList } from "../LibraryContentList";
import { LibraryHeader } from "../LibraryHeader";
import styles from "./Library.module.css";

export function Library() {
	const { contents } = libraryService.getLibrary();

	return (
		<div className={styles.root}>
			<LibraryHeader />
			{contents.length === 0 ? (
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
