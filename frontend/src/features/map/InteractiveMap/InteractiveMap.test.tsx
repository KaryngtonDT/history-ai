import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { InteractiveMap, type MapPlace } from "./InteractiveMap";

const places: MapPlace[] = [
	{
		name: "Rome",
		coordinates: { latitude: 41.9028, longitude: 12.4964 },
		description: "753 BC — Foundation of Rome",
	},
	{
		name: "Athens",
		coordinates: { latitude: 37.9838, longitude: 23.7275 },
		description: "Trade with Athens",
	},
];

describe("InteractiveMap", () => {
	it("renders historical places with coordinates and descriptions", () => {
		render(<InteractiveMap places={places} />);

		expect(
			screen.getByRole("region", { name: "Historical places map" }),
		).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Rome" })).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Athens" })).toBeInTheDocument();
		expect(screen.getByText("753 BC — Foundation of Rome")).toBeInTheDocument();
		expect(screen.getByText("Trade with Athens")).toBeInTheDocument();
		expect(screen.getAllByText("Latitude")).toHaveLength(2);
		expect(screen.getAllByText("Longitude")).toHaveLength(2);
		expect(screen.getByText("41.9028")).toBeInTheDocument();
		expect(screen.getByText("12.4964")).toBeInTheDocument();
	});

	it("preserves place order", () => {
		render(<InteractiveMap places={places} />);

		const headings = screen.getAllByRole("heading", { level: 3 });
		expect(headings.map((heading) => heading.textContent)).toEqual([
			"Rome",
			"Athens",
		]);
	});
});
