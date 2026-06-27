import type { ComponentType } from "react";
import type { Artifact } from "@/services/artifact/types";

export interface ArtifactRendererProps {
	artifact: Artifact | null;
	contentId: string;
	readOnly?: boolean;
}

export type ArtifactRenderer = ComponentType<ArtifactRendererProps>;
