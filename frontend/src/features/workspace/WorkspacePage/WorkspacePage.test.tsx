import { render, screen } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_PROJECT } from "@/services/workspace/MockWorkspaceRepository";
import { BatchProgress } from "../BatchProgress";
import { ProjectCard } from "../ProjectCard";
import { VideoGrid } from "../VideoGrid";

describe("Workspace feature components", () => {
	it("renders project card with video count", () => {
		render(<ProjectCard project={MOCK_PREVIEW_PROJECT} />);

		expect(screen.getByText("Marketing Campaign")).toBeInTheDocument();
		expect(screen.getByText("3 videos")).toBeInTheDocument();
	});

	it("renders video grid with filenames and pipeline links", () => {
		render(
			<MemoryRouter>
				<VideoGrid videos={MOCK_PREVIEW_PROJECT.videos} />
			</MemoryRouter>,
		);

		expect(screen.getByText("Interview.mp4")).toBeInTheDocument();
		expect(screen.getByText("Podcast.mp4")).toBeInTheDocument();
		expect(screen.getByText("Demo.mp4")).toBeInTheDocument();
		expect(
			screen.getAllByRole("link", { name: "Open pipeline →" }),
		).toHaveLength(3);
	});

	it("renders batch progress bar and percentage", () => {
		render(
			<BatchProgress
				progress={MOCK_PREVIEW_PROJECT.batchProgress}
				status={MOCK_PREVIEW_PROJECT.batchStatus}
			/>,
		);

		expect(screen.getByText("Overall Progress")).toBeInTheDocument();
		expect(screen.getByText("63%")).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"63",
		);
	});
});
