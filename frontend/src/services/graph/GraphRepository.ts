import type { GraphNeighborhood, KnowledgeGraph } from "./types";

export interface GraphRepository {
	getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph>;
	getGraphNeighborhood(
		contentId: string,
		artifactId: string,
	): Promise<GraphNeighborhood | null>;
	getConversationGraph(conversationId: string): Promise<KnowledgeGraph>;
}
