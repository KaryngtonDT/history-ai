import { useState } from "react";
import { Button } from "@/components/ui/Button";
import type { Artifact } from "@/services/artifact/types";
import { deriveLibraryItemTitle } from "@/services/library/deriveLibraryItemTitle";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItemType } from "@/services/library/types";
import styles from "./SaveToLibraryAction.module.css";

interface SaveToLibraryActionProps {
	artifact: Artifact;
	contentId: string;
}

type SaveState = "idle" | "saving" | "success" | "error";

function mapArtifactTypeToLibraryItemType(
	type: Artifact["type"],
): LibraryItemType {
	return type as LibraryItemType;
}

export function SaveToLibraryAction({
	artifact,
	contentId,
}: SaveToLibraryActionProps) {
	const [saveState, setSaveState] = useState<SaveState>("idle");

	const handleSave = () => {
		setSaveState("saving");

		void libraryService
			.addItem({
				contentId,
				artifactId: artifact.id,
				type: mapArtifactTypeToLibraryItemType(artifact.type),
				title: deriveLibraryItemTitle(artifact.type),
			})
			.then(() => {
				setSaveState("success");
			})
			.catch(() => {
				setSaveState("error");
			});
	};

	if (saveState === "success") {
		return <p className={styles.success}>Saved to Library</p>;
	}

	return (
		<div className={styles.root}>
			<Button
				variant="secondary"
				size="sm"
				disabled={saveState === "saving"}
				onClick={handleSave}
			>
				{saveState === "saving" ? "Saving..." : "Save to Library"}
			</Button>
			{saveState === "error" ? (
				<p className={styles.error}>Could not save to library.</p>
			) : null}
		</div>
	);
}
