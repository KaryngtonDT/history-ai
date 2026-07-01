/**
 * Localization audit helper — scans frontend/src for likely hardcoded UI strings.
 *
 * Run: node frontend/scripts/localization-audit.mjs
 */

import { readdirSync, readFileSync, statSync } from "node:fs";
import { dirname, join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const SRC_ROOT = join(__dirname, "..", "src");
const PROJECT_ROOT = join(__dirname, "..");

const ALLOWLIST_PATTERNS = [
	/^src\/i18n\//,
	/\.test\.(tsx|ts)$/,
	/\.module\.css$/,
	/\/types\.ts$/,
	/\/mock/i,
	/\/Mock[A-Z]/,
];

const TECHNICAL_TERMS = [
	"FasterWhisper",
	"Ollama",
	"F5-TTS",
	"OpenVoice",
	"LatentSync",
	"FFmpeg",
	"Lumen",
	"YouTube",
	"PDF",
	"MP4",
	"MOV",
	"MKV",
	"WAV",
	"API",
	"UUID",
	"Ctrl",
	"Esc",
];

function walk(dir, files = []) {
	for (const entry of readdirSync(dir)) {
		const full = join(dir, entry);
		const stat = statSync(full);

		if (stat.isDirectory()) {
			if (entry === "node_modules" || entry === "dist") {
				continue;
			}

			walk(full, files);
			continue;
		}

		if (/\.(tsx|ts)$/.test(entry)) {
			files.push(full);
		}
	}

	return files;
}

function isAllowlisted(relativePath) {
	return ALLOWLIST_PATTERNS.some((pattern) => pattern.test(relativePath));
}

function isTechnical(text) {
	return TECHNICAL_TERMS.some((term) => text.includes(term));
}

function scanFile(filePath) {
	const relativePath = relative(PROJECT_ROOT, filePath).replace(/\\/g, "/");

	if (isAllowlisted(relativePath)) {
		return [];
	}

	const content = readFileSync(filePath, "utf8");
	const findings = [];
	const lines = content.split("\n");

	for (let index = 0; index < lines.length; index += 1) {
		const line = lines[index];

		if (
			line.includes("t(") ||
			line.includes("useTranslation") ||
			line.includes("//") ||
			line.includes("import ") ||
			line.includes("console.")
		) {
			continue;
		}

		const jsxText = line.match(/>\s*([A-Z][A-Za-z0-9 ,.'!?…→:+-]{2,80})\s*</);

		if (jsxText) {
			const text = jsxText[1].trim();

			if (!isTechnical(text) && !text.startsWith("/api")) {
				findings.push({ line: index + 1, text });
			}
		}
	}

	return findings.map((finding) => ({ file: relativePath, ...finding }));
}

const files = walk(SRC_ROOT);
const allFindings = files.flatMap(scanFile);

console.log(`Localization audit — ${files.length} source files scanned`);
console.log(`Potential hardcoded UI strings: ${allFindings.length}`);

if (allFindings.length > 0) {
	console.log("\nSample findings (first 40):");
	for (const finding of allFindings.slice(0, 40)) {
		console.log(`  ${finding.file}:${finding.line} — "${finding.text}"`);
	}
}

process.exit(allFindings.length > 50 ? 1 : 0);
