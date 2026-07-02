import type {
	DecisionHistory,
	ExecutiveAgendaResponse,
	ExecutiveDashboard,
	ExecutiveDecision,
	ExecutiveDecisionActionRequest,
	ExecutiveRecommendationsResponse,
	ExecutiveResetRequest,
} from "./types";

export interface ShadowExecutiveRepository {
	getDashboard(scopeKey?: string): Promise<ExecutiveDashboard>;
	getAgenda(scopeKey?: string): Promise<ExecutiveAgendaResponse>;
	getRecommendations(
		scopeKey?: string,
	): Promise<ExecutiveRecommendationsResponse>;
	getHistory(scopeKey?: string): Promise<DecisionHistory>;
	approveDecision(
		id: string,
		request?: ExecutiveDecisionActionRequest,
	): Promise<ExecutiveDecision>;
	rejectDecision(
		id: string,
		request?: ExecutiveDecisionActionRequest,
	): Promise<ExecutiveDecision>;
	deferDecision(
		id: string,
		request?: ExecutiveDecisionActionRequest,
	): Promise<ExecutiveDecision>;
	reset(request?: ExecutiveResetRequest): Promise<ExecutiveDashboard>;
}
