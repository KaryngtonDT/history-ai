import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { PageIntroduction } from "./PageIntroduction";

describe("PageIntroduction", () => {
	it("renders title, description, and help box", () => {
		render(
			<PageIntroduction
				title="Upload Video"
				description="Start the pipeline."
				whatCanIDo="Upload a source video and follow the guided steps."
			/>,
		);

		expect(
			screen.getByRole("heading", { name: "Upload Video" }),
		).toBeInTheDocument();
		expect(screen.getByText("Start the pipeline.")).toBeInTheDocument();
		expect(screen.getByText("What can I do here?")).toBeInTheDocument();
	});
});
