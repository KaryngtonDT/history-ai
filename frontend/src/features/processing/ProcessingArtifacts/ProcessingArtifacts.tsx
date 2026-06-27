import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import {
	ARTIFACT_DISPLAY_ORDER,
	getArtifactRenderer,
	isKnownArtifactType,
	UnsupportedArtifactRenderer,
} from "@/features/processing/artifactRenderers";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingArtifacts.module.css";

interface ProcessingArtifactsProps {
	contentId: string;
}

function findArtifactByType(
	artifacts: Artifact[],
	type: Artifact["type"],
): Artifact | null {
	return artifacts.find((artifact) => artifact.type === type) ?? null;
}

export function ProcessingArtifacts({ contentId }: ProcessingArtifactsProps) {
	const [artifacts, setArtifacts] = useState<Artifact[]>([]);
	const [loading, setLoading] = useState(true);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [isEmpty, setIsEmpty] = useState(false);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setLoadError(null);
		setIsEmpty(false);
		setArtifacts([]);

		void artifactService
			.listByContentId(contentId)
			.then((loadedArtifacts) => {
				if (cancelled) {
					return;
				}

				if (loadedArtifacts.length === 0) {
					setIsEmpty(true);
					setArtifacts([]);
					return;
				}

				setArtifacts(loadedArtifacts);
				setIsEmpty(false);
			})
			.catch(() => {
				if (!cancelled) {
					setLoadError(
						"Could not load artifacts. Check that the backend is running.",
					);
					setArtifacts([]);
					setIsEmpty(false);
				}
			})
			.finally(() => {
				if (!cancelled) {
					setLoading(false);
				}
			});

		return () => {
			cancelled = true;
		};
	}, [contentId]);

	if (loading) {
		return (
			<div className={styles.loading}>
				<Spinner label="Loading artifacts" />
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState title="Unable to load artifacts" description={loadError} />
		);
	}

	if (isEmpty) {
		return (
			<EmptyState
				title="No artifacts yet"
				description="Generated learning artifacts will appear here once processing output is available."
			/>
		);
	}

	const unsupportedArtifacts = artifacts.filter(
		(artifact) => !isKnownArtifactType(artifact.type),
	);

	return (
		<div className={styles.list}>
			{ARTIFACT_DISPLAY_ORDER.map((type) => {
				const Renderer = getArtifactRenderer(type);
				const artifact = findArtifactByType(artifacts, type);

				return <Renderer key={type} artifact={artifact} />;
			})}
			{unsupportedArtifacts.map((artifact) => (
				<UnsupportedArtifactRenderer key={artifact.id} artifact={artifact} />
			))}
		</div>
	);
}
