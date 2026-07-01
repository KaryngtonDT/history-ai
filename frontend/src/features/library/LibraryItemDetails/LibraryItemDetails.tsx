import { useEffect, useState } from "react";
import { Link, useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { getArtifactRenderer } from "@/features/processing/artifactRenderers";
import { SeeAlsoRecommendationsPanel } from "@/features/recommendation/SeeAlsoRecommendationsPanel";
import { useTranslation } from "@/i18n";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItem } from "@/services/library/types";
import styles from "./LibraryItemDetails.module.css";

interface LoadedLibraryItemDetails {
	item: LibraryItem;
	artifact: Artifact;
}

export function LibraryItemDetails() {
	const { t } = useTranslation();
	const { libraryItemId } = useParams();
	const [details, setDetails] = useState<LoadedLibraryItemDetails | null>(null);
	const [loading, setLoading] = useState(true);
	const [itemNotFound, setItemNotFound] = useState(false);
	const [artifactNotFound, setArtifactNotFound] = useState(false);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		if (!libraryItemId) {
			setLoading(false);
			setItemNotFound(true);
			return;
		}

		let cancelled = false;

		setLoading(true);
		setDetails(null);
		setItemNotFound(false);
		setArtifactNotFound(false);
		setLoadError(null);

		void libraryService
			.listItems()
			.then((items) => {
				if (cancelled) {
					return;
				}

				const item = items.find(
					(libraryItem) => libraryItem.id === libraryItemId,
				);

				if (!item) {
					setItemNotFound(true);
					setLoading(false);
					return;
				}

				return artifactService
					.listByContentId(item.contentId)
					.then((artifacts) => {
						if (cancelled) {
							return;
						}

						const artifact = artifacts.find(
							(candidate) => candidate.id === item.artifactId,
						);

						if (!artifact) {
							setArtifactNotFound(true);
							setLoading(false);
							return;
						}

						setDetails({ item, artifact });
						setLoading(false);
					});
			})
			.catch(() => {
				if (!cancelled) {
					setLoadError(t("workspace.page.backendUnavailable"));
					setLoading(false);
				}
			});

		return () => {
			cancelled = true;
		};
	}, [libraryItemId, t]);

	if (loading) {
		return (
			<div className={styles.loading}>
				<Spinner label={t("workspace.library.itemDetails.loading")} />
			</div>
		);
	}

	if (itemNotFound) {
		return (
			<EmptyState
				title={t("workspace.library.itemDetails.notFoundTitle")}
				description={t("workspace.library.itemDetails.notFoundDescription")}
			/>
		);
	}

	if (artifactNotFound) {
		return (
			<EmptyState
				title={t("workspace.library.itemDetails.artifactNotFoundTitle")}
				description={t(
					"workspace.library.itemDetails.artifactNotFoundDescription",
				)}
			/>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState
				title={t("workspace.library.itemDetails.unableToLoad")}
				description={loadError}
			/>
		);
	}

	if (!details) {
		return null;
	}

	const Renderer = getArtifactRenderer(details.artifact.type);

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<Link to="/library" className={styles.backLink}>
					{t("workspace.library.itemDetails.backToLibrary")}
				</Link>
				<h2 className={styles.title}>{details.item.title}</h2>
			</header>
			<div className={styles.content}>
				<Renderer
					artifact={details.artifact}
					contentId={details.item.contentId}
					readOnly
				/>
				<SeeAlsoRecommendationsPanel
					contentId={details.item.contentId}
					artifactId={details.artifact.id}
				/>
			</div>
		</div>
	);
}
