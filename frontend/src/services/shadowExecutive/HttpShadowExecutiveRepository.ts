import {
	SHADOW_EXECUTIVE_AGENDA_PATH,
	SHADOW_EXECUTIVE_HISTORY_PATH,
	SHADOW_EXECUTIVE_PATH,
	SHADOW_EXECUTIVE_RECOMMENDATIONS_PATH,
	SHADOW_EXECUTIVE_RESET_PATH,
	shadowExecutiveDecisionApprovePath,
	shadowExecutiveDecisionDeferPath,
	shadowExecutiveDecisionRejectPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowExecutiveRepository } from "./ShadowExecutiveRepository";
import type {
	DecisionHistory,
	ExecutiveAgendaResponse,
	ExecutiveDashboard,
	ExecutiveDecision,
	ExecutiveDecisionActionRequest,
	ExecutiveRecommendationsResponse,
	ExecutiveResetRequest,
} from "./types";

export class HttpShadowExecutiveRepository
	implements ShadowExecutiveRepository
{
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getDashboard(scopeKey?: string): Promise<ExecutiveDashboard> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<ExecutiveDashboard>(
			`${SHADOW_EXECUTIVE_PATH}${query}`,
		);
	}

	getAgenda(scopeKey?: string): Promise<ExecutiveAgendaResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<ExecutiveAgendaResponse>(
			`${SHADOW_EXECUTIVE_AGENDA_PATH}${query}`,
		);
	}

	getRecommendations(
		scopeKey?: string,
	): Promise<ExecutiveRecommendationsResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<ExecutiveRecommendationsResponse>(
			`${SHADOW_EXECUTIVE_RECOMMENDATIONS_PATH}${query}`,
		);
	}

	getHistory(scopeKey?: string): Promise<DecisionHistory> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<DecisionHistory>(
			`${SHADOW_EXECUTIVE_HISTORY_PATH}${query}`,
		);
	}

	approveDecision(
		id: string,
		request: ExecutiveDecisionActionRequest = {},
	): Promise<ExecutiveDecision> {
		return this.httpClient.post<ExecutiveDecision>(
			shadowExecutiveDecisionApprovePath(id),
			request,
		);
	}

	rejectDecision(
		id: string,
		request: ExecutiveDecisionActionRequest = {},
	): Promise<ExecutiveDecision> {
		return this.httpClient.post<ExecutiveDecision>(
			shadowExecutiveDecisionRejectPath(id),
			request,
		);
	}

	deferDecision(
		id: string,
		request: ExecutiveDecisionActionRequest = {},
	): Promise<ExecutiveDecision> {
		return this.httpClient.post<ExecutiveDecision>(
			shadowExecutiveDecisionDeferPath(id),
			request,
		);
	}

	reset(request: ExecutiveResetRequest = {}): Promise<ExecutiveDashboard> {
		return this.httpClient.post<ExecutiveDashboard>(
			SHADOW_EXECUTIVE_RESET_PATH,
			request,
		);
	}
}
