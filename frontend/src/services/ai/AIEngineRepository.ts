import type { AIEngine } from "./types";

export interface AIEngineRepository {
	listEngines(): Promise<AIEngine[]>;
}
