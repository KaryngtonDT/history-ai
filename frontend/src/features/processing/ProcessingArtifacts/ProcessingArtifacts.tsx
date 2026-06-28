import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { ChatPanel } from "@/features/chat/ChatPanel";
import {
	CHAT_UNAVAILABLE_DESCRIPTION,
	CHAT_UNAVAILABLE_TITLE,
} from "@/features/chat/chatLabels";
import type { CitationClickDetails } from "@/features/chat/citationNavigation";
import { navigateToCitationTarget } from "@/features/chat/citationNavigation";
import { KnowledgeGraphPanel } from "@/features/graph/KnowledgeGraphPanel";
import { ArtifactRelationsPanel } from "@/features/processing/ArtifactRelationsPanel";
import {
	ARTIFACT_DISPLAY_ORDER,
	getArtifactRenderer,
	isKnownArtifactType,
	UnsupportedArtifactRenderer,
} from "@/features/processing/artifactRenderers";
import { SeeAlsoRecommendationsPanel } from "@/features/recommendation/SeeAlsoRecommendationsPanel";
import { SemanticSearchPanel } from "@/features/semantic/SemanticSearchPanel";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import { resolveChatContentId } from "@/shared/contentId";
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
	const clearHighlightRef = useRef<(() => void) | null>(null);

	const handleCitationClick = useCallback(
		(details: CitationClickDetails) => {
			clearHighlightRef.current?.();
			clearHighlightRef.current =
				navigateToCitationTarget(details.artifactId, artifacts) ?? null;
		},
		[artifacts],
	);

	useEffect(() => {
		return () => {
			clearHighlightRef.current?.();
		};
	}, []);

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

	const chatContentId = useMemo(
		() => resolveChatContentId(contentId, artifacts),
		[contentId, artifacts],
	);

	const chatSection =
		chatContentId !== null ? (
			<ChatPanel
				contentId={chatContentId}
				artifacts={artifacts}
				onCitationClick={handleCitationClick}
			/>
		) : (
			<EmptyState
				title={CHAT_UNAVAILABLE_TITLE}
				description={CHAT_UNAVAILABLE_DESCRIPTION}
			/>
		);

	if (loading) {
		return (
			<div className={styles.list}>
				<div className={styles.loading}>
					<Spinner label="Loading artifacts" />
				</div>
				{chatSection}
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<div className={styles.list}>
				<EmptyState title="Unable to load artifacts" description={loadError} />
				{chatSection}
			</div>
		);
	}

	if (isEmpty) {
		return (
			<div className={styles.list}>
				<EmptyState
					title="No artifacts yet"
					description="Generated learning artifacts will appear here once processing output is available."
				/>
				{chatSection}
			</div>
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

				return (
					<div
						key={type}
						id={artifact !== null ? `artifact-${type}` : undefined}
					>
						<Renderer artifact={artifact} contentId={contentId} />
						{artifact !== null ? (
							<SeeAlsoRecommendationsPanel
								contentId={contentId}
								artifactId={artifact.id}
							/>
						) : null}
					</div>
				);
			})}
			{unsupportedArtifacts.map((artifact) => (
				<div key={artifact.id}>
					<UnsupportedArtifactRenderer
						artifact={artifact}
						contentId={contentId}
					/>
					<SeeAlsoRecommendationsPanel
						contentId={contentId}
						artifactId={artifact.id}
					/>
				</div>
			))}
			<ArtifactRelationsPanel contentId={contentId} artifacts={artifacts} />
			<KnowledgeGraphPanel contentId={contentId} />
			<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />
			{chatSection}
		</div>
	);
}
