import { describe, expect, it, vi } from "vitest";
import type { AgentRepository } from "./AgentRepository";
import { AgentService } from "./AgentService";
import { buildMockAgentExecution, EMPTY_AGENT_EXECUTION } from "./types";

function createRepositoryMock(
	overrides: Partial<AgentRepository> = {},
): AgentRepository {
	return {
		runAgent: vi
			.fn()
			.mockResolvedValue(buildMockAgentExecution("What is Rome?")),
		...overrides,
	};
}

describe("AgentService", () => {
	it("returns empty execution for invalid content id", async () => {
		const repository = createRepositoryMock();
		const service = new AgentService(repository);

		const result = await service.runAgent("not-a-valid-uuid", "What is Rome?");

		expect(result).toEqual(EMPTY_AGENT_EXECUTION);
		expect(repository.runAgent).not.toHaveBeenCalled();
	});

	it("returns empty execution for invalid optional conversation id", async () => {
		const repository = createRepositoryMock();
		const service = new AgentService(repository);

		const result = await service.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
			"not-a-valid-uuid",
		);

		expect(result).toEqual(EMPTY_AGENT_EXECUTION);
		expect(repository.runAgent).not.toHaveBeenCalled();
	});

	it("returns empty execution for empty question", async () => {
		const repository = createRepositoryMock();
		const service = new AgentService(repository);

		const result = await service.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"   ",
		);

		expect(result).toEqual(EMPTY_AGENT_EXECUTION);
		expect(repository.runAgent).not.toHaveBeenCalled();
	});

	it("returns empty execution for too-long question", async () => {
		const repository = createRepositoryMock();
		const service = new AgentService(repository);

		const result = await service.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"a".repeat(2001),
		);

		expect(result).toEqual(EMPTY_AGENT_EXECUTION);
		expect(repository.runAgent).not.toHaveBeenCalled();
	});

	it("delegates valid requests to repository", async () => {
		const execution = buildMockAgentExecution("Compare Rome versus Byzantium");
		const runAgent = vi.fn().mockResolvedValue(execution);
		const service = new AgentService(createRepositoryMock({ runAgent }));

		const result = await service.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			" Compare Rome versus Byzantium ",
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(runAgent).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"Compare Rome versus Byzantium",
			"550e8400-e29b-41d4-a716-446655440001",
		);
		expect(result).toEqual(execution);
	});
});
