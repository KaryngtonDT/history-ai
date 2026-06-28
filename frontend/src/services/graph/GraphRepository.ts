import type { KnowledgeGraph } from "./types";

export interface GraphRepository {
	getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph>;
}
