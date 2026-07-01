import { useCallback, useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import { collectionService } from "@/services/collection/CollectionService";
import type { Collection } from "@/services/collection/types";
import { CollectionHeader } from "../CollectionHeader";
import { CollectionList } from "../CollectionList";
import { CreateCollectionDialog } from "../CreateCollectionDialog";
import styles from "./Collections.module.css";

export function Collections() {
	const { t } = useTranslation();
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
				setLoadError(t("workspace.page.backendUnavailable"));
			});
	}, [t]);

	useEffect(() => {
		loadCollections();
	}, [loadCollections]);

	return (
		<div className={styles.root}>
			<CollectionHeader onCreateClick={() => setCreateDialogOpen(true)} />
			{collections === null ? (
				<div className={styles.loading}>
					<Spinner label={t("workspace.collections.loadingCollections")} />
				</div>
			) : loadError !== null ? (
				<EmptyState
					title={t("workspace.collections.unableToLoadCollections")}
					description={loadError}
				/>
			) : collections.length === 0 ? (
				<EmptyState
					title={t("workspace.collections.noCollectionsYet")}
					description={t("workspace.collections.noCollectionsDescription")}
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
