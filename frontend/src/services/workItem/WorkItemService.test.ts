import { describe, expect, it } from "vitest";
import { MockWorkItemRepository } from "./MockWorkItemRepository";
import { WorkItemService } from "./WorkItemService";
import { mapContentToWorkItem } from "./workItemMappers";

describe("WorkItem read model", () => {
	it("maps content to work item with valid open route", () => {
		const item = mapContentToWorkItem({
			id: "1",
			title: "The Roman Empire",
			sourceType: "pdf",
			status: "processing",
			progress: 62,
		});

		expect(item.type).toBe("pdf");
		expect(item.openRoute).toBe("/processing/1");
		expect(item.primaryActionRoute).toBe("/processing/1");
	});

	it("maps video content to video overview route", () => {
		const item = mapContentToWorkItem({
			id: "vid-1",
			title: "Interview",
			sourceType: "video",
			status: "completed",
			progress: 100,
		});

		expect(item.openRoute).toBe("/video/vid-1");
	});

	it("returns recent work from mock repository", async () => {
		const service = new WorkItemService(new MockWorkItemRepository());
		const items = await service.listRecentWork();

		expect(items.length).toBeGreaterThan(0);
		expect(items.every((item) => item.openRoute.startsWith("/"))).toBe(true);
	});

	it("returns continue work for processing item", async () => {
		const service = new WorkItemService(new MockWorkItemRepository());
		const summary = await service.getSummary();

		expect(summary.continueWork).not.toBeNull();
		expect(summary.recentWork.length).toBeGreaterThan(0);
	});
});
