import type { ShadowExecutiveRepository } from "./ShadowExecutiveRepository";
import { createShadowExecutiveRepository } from "./ShadowExecutiveRepositoryFactory";
import type {
	ExecutiveDecisionActionRequest,
	ExecutiveResetRequest,
} from "./types";

export class ShadowExecutiveService {
	private readonly repository: ShadowExecutiveRepository;

	constructor(
		repository: ShadowExecutiveRepository = createShadowExecutiveRepository(),
	) {
		this.repository = repository;
	}

	getDashboard(scopeKey?: string) {
		return this.repository.getDashboard(scopeKey);
	}

	getAgenda(scopeKey?: string) {
		return this.repository.getAgenda(scopeKey);
	}

	getRecommendations(scopeKey?: string) {
		return this.repository.getRecommendations(scopeKey);
	}

	getHistory(scopeKey?: string) {
		return this.repository.getHistory(scopeKey);
	}

	approveDecision(id: string, request?: ExecutiveDecisionActionRequest) {
		return this.repository.approveDecision(id, request);
	}

	rejectDecision(id: string, request?: ExecutiveDecisionActionRequest) {
		return this.repository.rejectDecision(id, request);
	}

	deferDecision(id: string, request?: ExecutiveDecisionActionRequest) {
		return this.repository.deferDecision(id, request);
	}

	reset(request?: ExecutiveResetRequest) {
		return this.repository.reset(request);
	}
}

export const shadowExecutiveService = new ShadowExecutiveService();
