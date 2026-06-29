import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { buildMockAgentExecution } from "@/services/agent/types";
import { AgentModePanel } from "./AgentModePanel";

const { mockRunAgent } = vi.hoisted(() => ({
	mockRunAgent: vi.fn(),
}));

vi.mock("@/services/agent/AgentService", () => ({
	agentService: {
		runAgent: mockRunAgent,
	},
}));

describe("AgentModePanel", () => {
	beforeEach(() => {
		mockRunAgent.mockReset();
	});

	it("shows idle empty state initially", () => {
		render(<AgentModePanel contentId="550e8400-e29b-41d4-a716-446655440000" />);

		expect(screen.getByText("Agent Mode")).toBeInTheDocument();
		expect(screen.getByText("No agent run yet")).toBeInTheDocument();
		expect(screen.getByRole("button", { name: "Run agent" })).toBeDisabled();
	});

	it("calls AgentService and renders execution trace on submit", async () => {
		const user = userEvent.setup();
		const execution = buildMockAgentExecution("Compare Rome and Byzantium");
		mockRunAgent.mockResolvedValue(execution);

		render(<AgentModePanel contentId="550e8400-e29b-41d4-a716-446655440000" />);

		await user.type(
			screen.getByPlaceholderText("Compare Rome and Byzantium"),
			"Compare Rome and Byzantium",
		);
		await user.click(screen.getByRole("button", { name: "Run agent" }));

		await waitFor(() => {
			expect(mockRunAgent).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
				"Compare Rome and Byzantium",
				undefined,
			);
		});

		expect(screen.getByText("Plan")).toBeInTheDocument();
		expect(screen.getByText("Execution")).toBeInTheDocument();
		expect(screen.getByText("Agent workflow completed.")).toBeInTheDocument();
	});

	it("shows loading state while agent runs", async () => {
		const user = userEvent.setup();
		mockRunAgent.mockReturnValue(new Promise(() => {}));

		render(<AgentModePanel contentId="550e8400-e29b-41d4-a716-446655440000" />);

		await user.type(
			screen.getByPlaceholderText("Compare Rome and Byzantium"),
			"What is Rome?",
		);
		await user.click(screen.getByRole("button", { name: "Run agent" }));

		expect(
			screen.getByRole("status", { name: "Running agent workflow" }),
		).toBeInTheDocument();
	});

	it("shows error state when agent returns empty execution", async () => {
		const user = userEvent.setup();
		mockRunAgent.mockResolvedValue({ plan: [], steps: [], finalSummary: "" });

		render(<AgentModePanel contentId="550e8400-e29b-41d4-a716-446655440000" />);

		await user.type(
			screen.getByPlaceholderText("Compare Rome and Byzantium"),
			"What is Rome?",
		);
		await user.click(screen.getByRole("button", { name: "Run agent" }));

		expect(
			await screen.findByText(
				"Unable to run the agent workflow for this question.",
			),
		).toBeInTheDocument();
	});

	it("passes conversationId to AgentService when provided", async () => {
		const user = userEvent.setup();
		mockRunAgent.mockResolvedValue(buildMockAgentExecution("What is Rome?"));

		render(
			<AgentModePanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				conversationId="550e8400-e29b-41d4-a716-446655440001"
			/>,
		);

		await user.type(
			screen.getByPlaceholderText("Compare Rome and Byzantium"),
			"What is Rome?",
		);
		await user.click(screen.getByRole("button", { name: "Run agent" }));

		await waitFor(() => {
			expect(mockRunAgent).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
				"What is Rome?",
				"550e8400-e29b-41d4-a716-446655440001",
			);
		});
	});
});
