import { render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { TimelineMapPanel } from "./TimelineMapPanel";

const { mockGetTimelineMap } = vi.hoisted(() => ({
	mockGetTimelineMap: vi.fn(),
}));

vi.mock("@/services/map/MapService", () => ({
	mapService: {
		getTimelineMap: mockGetTimelineMap,
	},
}));

const places = [
	{
		name: "Rome",
		coordinates: { latitude: 41.9028, longitude: 12.4964 },
		description: "753 BC — Foundation of Rome",
	},
];

describe("TimelineMapPanel", () => {
	beforeEach(() => {
		mockGetTimelineMap.mockReset();
	});

	it("calls MapService with artifact id", async () => {
		mockGetTimelineMap.mockResolvedValue(places);

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await waitFor(() => {
			expect(mockGetTimelineMap).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
			);
		});
	});

	it("shows loading state while map data loads", () => {
		mockGetTimelineMap.mockReturnValue(new Promise(() => {}));

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(
			screen.getByRole("status", { name: "Loading map" }),
		).toBeInTheDocument();
	});

	it("renders InteractiveMap when places are returned", async () => {
		mockGetTimelineMap.mockResolvedValue(places);

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(
			await screen.findByRole("region", { name: "Historical places map" }),
		).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Rome" })).toBeInTheDocument();
	});

	it("shows empty state when map returns no places", async () => {
		mockGetTimelineMap.mockResolvedValue([]);

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("No places found")).toBeInTheDocument();
	});

	it("shows unavailable state when map returns null", async () => {
		mockGetTimelineMap.mockResolvedValue(null);

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Map unavailable")).toBeInTheDocument();
	});

	it("shows error state when MapService fails", async () => {
		mockGetTimelineMap.mockRejectedValue(new Error("Network error"));

		render(
			<TimelineMapPanel artifactId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Unable to load map")).toBeInTheDocument();
	});
});
