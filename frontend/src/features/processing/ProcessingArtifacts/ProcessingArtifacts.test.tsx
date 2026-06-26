import { render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { ProcessingArtifacts } from "@/features/processing/ProcessingArtifacts";
import { artifactService } from "@/services/artifact/ArtifactService";

describe("ProcessingArtifacts", () => {
	it("displays summary artifact content in a card", async () => {
		vi.spyOn(artifactService, "getSummaryArtifact").mockResolvedValue({
			id: "artifact-1",
			contentId: "content-1",
			processingJobId: "job-1",
			type: "summary",
			content: "Generated summary text",
			createdAt: "2026-06-26T12:00:00+00:00",
		});

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
		});
		expect(screen.getByText("Summary")).toBeInTheDocument();
	});

	it("shows empty state when no artifacts exist", async () => {
		vi.spyOn(artifactService, "getSummaryArtifact").mockResolvedValue(null);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("No artifacts yet")).toBeInTheDocument();
		});
	});
});
