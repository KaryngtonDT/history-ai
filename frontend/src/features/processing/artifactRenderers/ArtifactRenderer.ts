import type { ComponentType } from "react";
import type { Artifact } from "@/services/artifact/types";

export interface ArtifactRendererProps {
	artifact: Artifact | null;
}

export type ArtifactRenderer = ComponentType<ArtifactRendererProps>;
