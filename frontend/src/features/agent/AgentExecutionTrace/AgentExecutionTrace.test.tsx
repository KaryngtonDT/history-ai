import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { buildMockAgentExecution } from "@/services/agent/types";
import { AgentExecutionTrace } from "./AgentExecutionTrace";

describe("AgentExecutionTrace", () => {
	it("renders plan and execution steps in order", () => {
		const execution = buildMockAgentExecution("Compare Rome versus Byzantium");

		render(<AgentExecutionTrace execution={execution} />);

		expect(
			screen.getByRole("region", { name: "Agent plan" }),
		).toBeInTheDocument();
		expect(
			screen.getByRole("region", { name: "Agent execution" }),
		).toBeInTheDocument();
		expect(screen.getByText("Plan")).toBeInTheDocument();
		expect(screen.getByText("Execution")).toBeInTheDocument();
		expect(screen.getByText("Metadata")).toBeInTheDocument();
		expect(screen.getAllByText("Semantic Search")).toHaveLength(3);
		expect(screen.getAllByText("Knowledge Graph")).toHaveLength(3);
		expect(screen.getAllByText("Multi-Document Chat")).toHaveLength(3);
		expect(screen.getByText("Chunks")).toBeInTheDocument();
		expect(screen.getByText("Nodes")).toBeInTheDocument();
		expect(screen.getAllByText("completed")).toHaveLength(3);
		expect(screen.getByText("Agent workflow completed.")).toBeInTheDocument();
	});
});
