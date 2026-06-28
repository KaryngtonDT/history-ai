import { readdirSync, readFileSync, statSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const FRONTEND_SRC = path.resolve(
	path.dirname(fileURLToPath(import.meta.url)),
	"..",
);

const HTTP_CLIENT_RELATIVE = "services/http/HttpClient.ts";

const EXCLUDED_PATH_PREFIXES = ["architecture/"];

const SOURCE_EXTENSIONS = new Set([".ts", ".tsx"]);

export interface ArchitectureViolation {
	rule: string;
	file: string;
	detail: string;
}

function collectSourceFiles(directory: string): string[] {
	const files: string[] = [];
	const entries = readdirSync(directory, { withFileTypes: true });

	for (const entry of entries) {
		const absolutePath = path.join(directory, entry.name);

		if (entry.isDirectory()) {
			if (entry.name === "node_modules" || entry.name === "dist") {
				continue;
			}

			files.push(...collectSourceFiles(absolutePath));
			continue;
		}

		if (!SOURCE_EXTENSIONS.has(path.extname(entry.name))) {
			continue;
		}

		files.push(absolutePath);
	}

	return files.sort();
}

function toRelativePath(absolutePath: string): string {
	return path.relative(FRONTEND_SRC, absolutePath).replaceAll("\\", "/");
}

function directoryExists(directory: string): boolean {
	try {
		return statSync(directory).isDirectory();
	} catch {
		return false;
	}
}

export function findFetchViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];

	for (const filePath of collectSourceFiles(FRONTEND_SRC)) {
		const relativePath = toRelativePath(filePath);

		if (relativePath === HTTP_CLIENT_RELATIVE) {
			continue;
		}

		if (
			EXCLUDED_PATH_PREFIXES.some((prefix) => relativePath.startsWith(prefix))
		) {
			continue;
		}

		const content = readFileSync(filePath, "utf8");

		if (/\bfetch\s*\(/.test(content)) {
			violations.push({
				rule: "fetch-centralization",
				file: relativePath,
				detail: "fetch() must only appear in HttpClient.ts",
			});
		}
	}

	return violations;
}

export function findFeatureHttpRepositoryViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const featuresRoot = path.join(FRONTEND_SRC, "features");

	if (!directoryExists(featuresRoot)) {
		return violations;
	}

	const forbiddenImportPattern =
		/from\s+["']@\/services\/[^"']*\/Http[^"']*Repository["']/;

	for (const filePath of collectSourceFiles(featuresRoot)) {
		const content = readFileSync(filePath, "utf8");

		if (!forbiddenImportPattern.test(content)) {
			continue;
		}

		violations.push({
			rule: "feature-http-repository",
			file: toRelativePath(filePath),
			detail: "Feature components must use services, not Http repositories",
		});
	}

	return violations;
}

export function findFeatureHttpClientViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const featuresRoot = path.join(FRONTEND_SRC, "features");

	if (!directoryExists(featuresRoot)) {
		return violations;
	}

	const forbiddenImportPattern = /from\s+["']@\/services\/http\/HttpClient["']/;

	for (const filePath of collectSourceFiles(featuresRoot)) {
		const content = readFileSync(filePath, "utf8");

		if (!forbiddenImportPattern.test(content)) {
			continue;
		}

		violations.push({
			rule: "feature-http-client",
			file: toRelativePath(filePath),
			detail: "Feature components must not import HttpClient directly",
		});
	}

	return violations;
}

export function findFeatureSearchTransportViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const featuresRoot = path.join(FRONTEND_SRC, "features");

	if (!directoryExists(featuresRoot)) {
		return violations;
	}

	const forbiddenPatterns: Array<{ pattern: RegExp; detail: string }> = [
		{
			pattern: /from\s+["']@\/services\/search\/HttpSearchRepository["']/,
			detail:
				"Feature components must use SearchService, not HttpSearchRepository",
		},
		{
			pattern: /from\s+["']@\/services\/search\/SearchRepositoryFactory["']/,
			detail: "Feature components must not wire SearchRepositoryFactory",
		},
		{
			pattern: /from\s+["']@\/services\/search\/SearchRepository["']/,
			detail: "Feature components must use SearchService, not SearchRepository",
		},
	];

	for (const filePath of collectSourceFiles(featuresRoot)) {
		const content = readFileSync(filePath, "utf8");

		for (const { pattern, detail } of forbiddenPatterns) {
			if (!pattern.test(content)) {
				continue;
			}

			violations.push({
				rule: "feature-search-transport",
				file: toRelativePath(filePath),
				detail,
			});
		}
	}

	return violations;
}

export function findFeatureTimelineTransportViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const featuresRoot = path.join(FRONTEND_SRC, "features");

	if (!directoryExists(featuresRoot)) {
		return violations;
	}

	const forbiddenPatterns: Array<{ pattern: RegExp; detail: string }> = [
		{
			pattern: /from\s+["']@\/services\/timeline\/HttpTimelineRepository["']/,
			detail:
				"Feature components must use TimelineService, not HttpTimelineRepository",
		},
		{
			pattern:
				/from\s+["']@\/services\/timeline\/TimelineRepositoryFactory["']/,
			detail: "Feature components must not wire TimelineRepositoryFactory",
		},
		{
			pattern: /from\s+["']@\/services\/timeline\/TimelineRepository["']/,
			detail:
				"Feature components must use TimelineService, not TimelineRepository",
		},
	];

	for (const filePath of collectSourceFiles(featuresRoot)) {
		const content = readFileSync(filePath, "utf8");

		for (const { pattern, detail } of forbiddenPatterns) {
			if (!pattern.test(content)) {
				continue;
			}

			violations.push({
				rule: "feature-timeline-transport",
				file: toRelativePath(filePath),
				detail,
			});
		}
	}

	return violations;
}

export function findInteractiveTimelineServiceViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const interactiveTimelineRoot = path.join(
		FRONTEND_SRC,
		"features/processing/InteractiveTimeline",
	);

	if (!directoryExists(interactiveTimelineRoot)) {
		return violations;
	}

	const forbiddenPatterns: Array<{ pattern: RegExp; detail: string }> = [
		{
			pattern: /from\s+["']@\/services\//,
			detail: "InteractiveTimeline must not import services",
		},
		{
			pattern: /Repository["']/,
			detail: "InteractiveTimeline must not import repositories",
		},
	];

	for (const filePath of collectSourceFiles(interactiveTimelineRoot)) {
		const content = readFileSync(filePath, "utf8");

		for (const { pattern, detail } of forbiddenPatterns) {
			if (!pattern.test(content)) {
				continue;
			}

			violations.push({
				rule: "interactive-timeline-props-only",
				file: toRelativePath(filePath),
				detail,
			});
		}
	}

	return violations;
}

export function findFeatureMapTransportViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const featuresRoot = path.join(FRONTEND_SRC, "features");

	if (!directoryExists(featuresRoot)) {
		return violations;
	}

	const forbiddenPatterns: Array<{ pattern: RegExp; detail: string }> = [
		{
			pattern: /from\s+["']@\/services\/map\/HttpMapRepository["']/,
			detail: "Feature components must use MapService, not HttpMapRepository",
		},
		{
			pattern: /from\s+["']@\/services\/map\/MapRepositoryFactory["']/,
			detail: "Feature components must not wire MapRepositoryFactory",
		},
		{
			pattern: /from\s+["']@\/services\/map\/MapRepository["']/,
			detail: "Feature components must use MapService, not MapRepository",
		},
	];

	for (const filePath of collectSourceFiles(featuresRoot)) {
		const content = readFileSync(filePath, "utf8");

		for (const { pattern, detail } of forbiddenPatterns) {
			if (!pattern.test(content)) {
				continue;
			}

			violations.push({
				rule: "feature-map-transport",
				file: toRelativePath(filePath),
				detail,
			});
		}
	}

	return violations;
}

export function findInteractiveMapServiceViolations(): ArchitectureViolation[] {
	const violations: ArchitectureViolation[] = [];
	const interactiveMapRoot = path.join(
		FRONTEND_SRC,
		"features/map/InteractiveMap",
	);

	if (!directoryExists(interactiveMapRoot)) {
		return violations;
	}

	const forbiddenPatterns: Array<{ pattern: RegExp; detail: string }> = [
		{
			pattern: /from\s+["']@\/services\//,
			detail: "InteractiveMap must not import services",
		},
		{
			pattern: /Repository["']/,
			detail: "InteractiveMap must not import repositories",
		},
	];

	for (const filePath of collectSourceFiles(interactiveMapRoot)) {
		const content = readFileSync(filePath, "utf8");

		for (const { pattern, detail } of forbiddenPatterns) {
			if (!pattern.test(content)) {
				continue;
			}

			violations.push({
				rule: "interactive-map-props-only",
				file: toRelativePath(filePath),
				detail,
			});
		}
	}

	return violations;
}

export function collectArchitectureViolations(): ArchitectureViolation[] {
	return [
		...findFetchViolations(),
		...findFeatureHttpRepositoryViolations(),
		...findFeatureHttpClientViolations(),
		...findFeatureSearchTransportViolations(),
		...findFeatureTimelineTransportViolations(),
		...findFeatureMapTransportViolations(),
		...findInteractiveTimelineServiceViolations(),
		...findInteractiveMapServiceViolations(),
	];
}
