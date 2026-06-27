import { useCallback, useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { collectionService } from "@/services/collection/CollectionService";
import type { Collection } from "@/services/collection/types";
import { CollectionHeader } from "../CollectionHeader";
import { CollectionList } from "../CollectionList";
import { CreateCollectionDialog } from "../CreateCollectionDialog";
import styles from "./Collections.module.css";

export function Collections() {
	const [collections, setCollections] = useState<Collection[] | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [createDialogOpen, setCreateDialogOpen] = useState(false);

	const loadCollections = useCallback(() => {
		setCollections(null);
		setLoadError(null);

		void collectionService
			.listCollections()
			.then((loadedCollections) => {
				setCollections(loadedCollections);
				setLoadError(null);
			})
			.catch(() => {
				setCollections([]);
				setLoadError(
					"Could not reach the server. Check that the backend is running.",
				);
			});
	}, []);

	useEffect(() => {
		loadCollections();
	}, [loadCollections]);

	return (
		<div className={styles.root}>
			<CollectionHeader onCreateClick={() => setCreateDialogOpen(true)} />
			{collections === null ? (
				<div className={styles.loading}>
					<Spinner label="Loading collections" />
				</div>
			) : loadError !== null ? (
				<EmptyState
					title="Unable to load collections"
					description={loadError}
				/>
			) : collections.length === 0 ? (
				<EmptyState
					title="No collections yet"
					description="Create your first collection to organize library items."
				/>
			) : (
				<CollectionList collections={collections} />
			)}
			<CreateCollectionDialog
				open={createDialogOpen}
				onClose={() => setCreateDialogOpen(false)}
				onCreated={loadCollections}
			/>
		</div>
	);
}
