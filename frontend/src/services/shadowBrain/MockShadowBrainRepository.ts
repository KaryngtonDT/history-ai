import type { ShadowBrainRepository } from "./ShadowBrainRepository";
import type {
	AddBookmarkRequest,
	AddNoteRequest,
	BrainDashboard,
	ConceptDetail,
	ConceptTreeResponse,
	KnowledgeBookmark,
	KnowledgeDiff,
	KnowledgeEntry,
	KnowledgeInsight,
	KnowledgeNote,
	KnowledgeRevision,
	KnowledgeSearchResponse,
	KnowledgeSource,
	KnowledgeTimelineEvent,
	KnowledgeTreeNode,
	KnowledgeWorkspace,
	RebuildWorkspaceRequest,
	TimelineResponse,
} from "./types";

const dockerEntry: KnowledgeEntry = {
	id: "entry-docker",
	conceptKey: "docker",
	label: "Docker",
	summary:
		"Container platform for packaging applications with dependencies and running them consistently across environments.",
	masteryPercent: 72,
	firstSeenAt: "2026-01-12T10:00:00+00:00",
	lastSeenAt: "2026-06-28T14:30:00+00:00",
	exposureCount: 41,
	exerciseCount: 12,
	explanationCount: 17,
	relatedKeys: ["containers", "docker_networking", "kubernetes"],
	recommendations: [
		"Review compose networking before Kubernetes ingress topics.",
	],
};

const containersEntry: KnowledgeEntry = {
	id: "entry-containers",
	conceptKey: "containers",
	label: "Containers",
	summary:
		"Isolated runtime environments sharing the host kernel — the foundation of Docker workloads.",
	masteryPercent: 81,
	firstSeenAt: "2026-01-12T10:15:00+00:00",
	lastSeenAt: "2026-06-20T09:00:00+00:00",
	exposureCount: 28,
	exerciseCount: 8,
	explanationCount: 11,
	relatedKeys: ["docker", "docker_compose"],
	recommendations: ["Practice multi-stage builds to reduce image size."],
};

const networkingEntry: KnowledgeEntry = {
	id: "entry-docker-networking",
	conceptKey: "docker_networking",
	label: "Docker Networking",
	summary:
		"Bridge, host, and overlay networks — how containers discover and communicate with each other.",
	masteryPercent: 58,
	firstSeenAt: "2026-02-03T16:00:00+00:00",
	lastSeenAt: "2026-06-01T11:00:00+00:00",
	exposureCount: 19,
	exerciseCount: 4,
	explanationCount: 6,
	relatedKeys: ["docker", "kubernetes"],
	recommendations: ["Schedule spaced revision — last seen 32 days ago."],
};

const kubernetesEntry: KnowledgeEntry = {
	id: "entry-kubernetes",
	conceptKey: "kubernetes",
	label: "Kubernetes",
	summary:
		"Orchestration layer for containerized workloads — pods, services, deployments, and ingress.",
	masteryPercent: 34,
	firstSeenAt: "2026-03-18T08:00:00+00:00",
	lastSeenAt: "2026-06-15T18:00:00+00:00",
	exposureCount: 14,
	exerciseCount: 2,
	explanationCount: 5,
	relatedKeys: ["docker", "docker_networking", "containers"],
	recommendations: [
		"Strengthen Docker networking before advanced ingress labs.",
	],
};

const cqrsEntry: KnowledgeEntry = {
	id: "entry-cqrs",
	conceptKey: "cqrs",
	label: "CQRS",
	summary:
		"Command Query Responsibility Segregation — separate models for writes and reads in complex domains.",
	masteryPercent: 46,
	firstSeenAt: "2026-04-10T12:00:00+00:00",
	lastSeenAt: "2026-05-22T15:00:00+00:00",
	exposureCount: 11,
	exerciseCount: 3,
	explanationCount: 4,
	relatedKeys: ["symfony_messenger", "event_sourcing"],
	recommendations: [
		"Pair with Symfony Messenger exercises for hands-on practice.",
	],
};

const entries: KnowledgeEntry[] = [
	dockerEntry,
	containersEntry,
	networkingEntry,
	kubernetesEntry,
	cqrsEntry,
];

const dockerSources: KnowledgeSource[] = [
	{
		id: "src-docker-video-1",
		type: "video",
		label: "Docker fundamentals #1",
		resourceId: "video-docker-fundamentals-1",
		resourceLabel: "Docker Fundamentals",
		conceptKey: "docker",
		occurredAt: "2026-01-12T10:00:00+00:00",
		detail: "Intro to images and containers",
		linkHint: "/video/video-docker-fundamentals-1/watch?t=120",
	},
	{
		id: "src-docker-pdf-1",
		type: "pdf",
		label: "Container cheat sheet",
		resourceId: "pdf-docker-cheatsheet",
		resourceLabel: "Docker Cheat Sheet",
		conceptKey: "docker",
		occurredAt: "2026-02-01T09:00:00+00:00",
		detail: "Page 3 — lifecycle commands",
		linkHint: "/library/pdf-docker-cheatsheet?page=3",
	},
	{
		id: "src-docker-conversation-1",
		type: "conversation",
		label: "Shadow Q&A — volumes vs bind mounts",
		resourceId: "conv-docker-volumes",
		resourceLabel: "Volumes discussion",
		conceptKey: "docker",
		occurredAt: "2026-03-05T19:00:00+00:00",
		detail: "Clarified persistence patterns",
		linkHint: null,
	},
	{
		id: "src-docker-mission-1",
		type: "mission",
		label: "Containerize Symfony app",
		resourceId: "mission-docker-symfony",
		resourceLabel: "Docker + Symfony mission",
		conceptKey: "docker",
		occurredAt: "2026-04-12T14:00:00+00:00",
		detail: "Multi-service compose stack",
		linkHint: "/settings/shadow/mentor",
	},
];

const insights: KnowledgeInsight[] = [
	{
		id: "insight-docker-gap",
		kind: "revision_gap",
		label: "Docker networking needs revision",
		detail:
			"docker_networking last seen 32 days ago — executive flagged before Kubernetes.",
		conceptKey: "docker_networking",
	},
	{
		id: "insight-cqrs-search",
		kind: "search_cluster",
		label: "CQRS appears across 3 sources",
		detail: "2 videos, 1 PDF, and 4 conversations mention CQRS patterns.",
		conceptKey: "cqrs",
	},
];

const revisions: KnowledgeRevision[] = [
	{
		conceptKey: "docker_networking",
		dueAt: "2026-07-01T09:00:00+00:00",
		reason: "Spaced repetition interval elapsed (28 days).",
	},
	{
		conceptKey: "cqrs",
		dueAt: "2026-07-05T09:00:00+00:00",
		reason: "Teaching checkpoint recommended review.",
	},
];

const timelineEvents: KnowledgeTimelineEvent[] = [
	{
		id: "timeline-jan-docker",
		label: "First Docker video completed",
		occurredAt: "2026-01-12T10:00:00+00:00",
		conceptKey: "docker",
		sourceType: "video",
		resourceId: "video-docker-fundamentals-1",
	},
	{
		id: "timeline-feb-networking",
		label: "Docker networking module",
		occurredAt: "2026-02-03T16:00:00+00:00",
		conceptKey: "docker_networking",
		sourceType: "video",
		resourceId: "video-docker-networking-2",
	},
	{
		id: "timeline-mar-k8s",
		label: "Kubernetes intro mission unlocked",
		occurredAt: "2026-03-18T08:00:00+00:00",
		conceptKey: "kubernetes",
		sourceType: "mission",
		resourceId: "mission-k8s-intro",
	},
	{
		id: "timeline-apr-cqrs",
		label: "CQRS explained in Symfony context",
		occurredAt: "2026-04-10T12:00:00+00:00",
		conceptKey: "cqrs",
		sourceType: "video",
		resourceId: "video-symfony-cqrs-4",
	},
];

const conceptTree: KnowledgeTreeNode[] = [
	{
		id: "tree-docker",
		label: "Docker",
		conceptKey: "docker",
		entryCount: 8,
		children: [
			{
				id: "tree-docker-videos",
				label: "8 videos",
				conceptKey: null,
				entryCount: 8,
				children: [],
			},
			{
				id: "tree-docker-pdfs",
				label: "3 PDFs",
				conceptKey: null,
				entryCount: 3,
				children: [],
			},
			{
				id: "tree-docker-conversations",
				label: "17 conversations",
				conceptKey: null,
				entryCount: 17,
				children: [],
			},
			{
				id: "tree-docker-exercises",
				label: "12 exercises",
				conceptKey: null,
				entryCount: 12,
				children: [],
			},
			{
				id: "tree-docker-missions",
				label: "4 missions",
				conceptKey: null,
				entryCount: 4,
				children: [],
			},
			{
				id: "tree-docker-errors",
				label: "2 frequent errors",
				conceptKey: null,
				entryCount: 2,
				children: [],
			},
			{
				id: "tree-kubernetes",
				label: "Kubernetes (related)",
				conceptKey: "kubernetes",
				entryCount: 1,
				children: [],
			},
			{
				id: "tree-containers",
				label: "Containers",
				conceptKey: "containers",
				entryCount: 1,
				children: [],
			},
			{
				id: "tree-networking",
				label: "Networking",
				conceptKey: "docker_networking",
				entryCount: 1,
				children: [],
			},
		],
	},
	{
		id: "tree-symfony",
		label: "Symfony",
		conceptKey: "symfony_kernel",
		entryCount: 6,
		children: [
			{
				id: "tree-cqrs",
				label: "CQRS",
				conceptKey: "cqrs",
				entryCount: 1,
				children: [],
			},
		],
	},
];

let bookmarks: KnowledgeBookmark[] = [
	{
		id: "bookmark-docker",
		label: "Docker fundamentals",
		tags: ["backend", "devops"],
		conceptKey: "docker",
		resourceType: null,
		resourceId: null,
	},
	{
		id: "bookmark-video-k8s",
		label: "Kubernetes ingress deep dive",
		tags: ["kubernetes", "watch-later"],
		conceptKey: null,
		resourceType: "video",
		resourceId: "video-k8s-ingress-9",
	},
];

let notes: KnowledgeNote[] = [
	{
		id: "note-docker-volumes",
		body: "Prefer named volumes for database data — bind mounts for local config only.",
		createdAt: "2026-03-06T10:00:00+00:00",
		conceptKey: "docker",
	},
	{
		id: "note-cqrs",
		body: "CQRS shines when read/write models diverge — not every CRUD app needs it.",
		createdAt: "2026-04-11T08:30:00+00:00",
		conceptKey: "cqrs",
	},
];

function buildWorkspace(): KnowledgeWorkspace {
	return {
		id: "77777777-7777-4777-8777-777777777777",
		scopeKey: "default",
		workspaceEnabled: true,
		lastSyncedAt: "2026-07-02T08:00:00+00:00",
		entries,
		bookmarks,
		notes,
		timeline: timelineEvents,
		statistics: {
			videoCount: 24,
			pdfCount: 9,
			conversationCount: 38,
			exerciseCount: 31,
			missionCount: 7,
			conceptCount: entries.length,
			domainHeatmap: [
				{ key: "docker", label: "Docker", percent: 88 },
				{ key: "symfony", label: "Symfony", percent: 74 },
				{ key: "php", label: "PHP", percent: 69 },
				{ key: "testing", label: "Testing", percent: 52 },
				{ key: "kubernetes", label: "Kubernetes", percent: 41 },
				{ key: "cqrs", label: "CQRS", percent: 28 },
			],
		},
	};
}

function buildDashboard(): BrainDashboard {
	return {
		scopeKey: "default",
		workspace: buildWorkspace(),
		insights,
		revisions,
	};
}

function findEntryByIdOrKey(id: string): KnowledgeEntry | undefined {
	return (
		entries.find((entry) => entry.id === id) ??
		entries.find((entry) => entry.conceptKey === id)
	);
}

function buildConceptDetail(entry: KnowledgeEntry): ConceptDetail {
	const related = entry.relatedKeys
		.map((key) => entries.find((item) => item.conceptKey === key))
		.filter((item): item is KnowledgeEntry => item !== undefined);

	const conceptNotes = notes.filter(
		(note) => note.conceptKey === entry.conceptKey,
	);

	const sources =
		entry.conceptKey === "docker"
			? dockerSources
			: [
					{
						id: `src-${entry.conceptKey}-video`,
						type: "video" as const,
						label: `${entry.label} overview`,
						resourceId: `video-${entry.conceptKey}-1`,
						resourceLabel: entry.label,
						conceptKey: entry.conceptKey,
						occurredAt: entry.firstSeenAt,
						detail: null,
						linkHint: null,
					},
				];

	return {
		scopeKey: "default",
		entry,
		sources,
		related,
		notes: conceptNotes,
		evolution: {
			conceptKey: entry.conceptKey,
			firstSeenAt: entry.firstSeenAt,
			explanationCount: entry.explanationCount,
			videoCount: entry.conceptKey === "docker" ? 8 : 2,
			exerciseCount: entry.exerciseCount,
			lastRevisionAt: entry.lastSeenAt,
			masteryPercent: entry.masteryPercent,
		},
	};
}

function buildDiff(resourceType: string, resourceId: string): KnowledgeDiff {
	const isLowRedundancy =
		resourceId.includes("k8s") || resourceId.includes("kubernetes");

	return {
		scopeKey: "default",
		resourceType,
		resourceId,
		resourceLabel:
			resourceId === "video-symfony-kernel-7"
				? "Symfony kernel request lifecycle"
				: "Kubernetes ingress deep dive",
		newConcepts: isLowRedundancy ? 8 : 3,
		knownConcepts: isLowRedundancy ? 17 : 22,
		revisionDue: 4,
		redundancyPercent: isLowRedundancy ? 15 : 85,
		redundancy: isLowRedundancy ? "low" : "high",
		novelConceptKeys: isLowRedundancy
			? ["ingress_controller", "tls_termination", "helm_charts"]
			: ["symfony_event_dispatcher"],
		knownConceptKeys: ["docker", "containers", "dependency_injection"],
		revisionConceptKeys: ["docker_networking", "cqrs"],
	};
}

export class MockShadowBrainRepository implements ShadowBrainRepository {
	getDashboard(): Promise<BrainDashboard> {
		return Promise.resolve(buildDashboard());
	}

	getConceptTree(): Promise<ConceptTreeResponse> {
		return Promise.resolve({
			scopeKey: "default",
			tree: conceptTree,
		});
	}

	getConcept(id: string): Promise<ConceptDetail> {
		const entry = findEntryByIdOrKey(id);

		if (!entry) {
			return Promise.reject(new Error("Concept not found."));
		}

		return Promise.resolve(buildConceptDetail(entry));
	}

	search(query: string): Promise<KnowledgeSearchResponse> {
		const normalized = query.trim().toLowerCase();
		const hits = entries
			.filter(
				(entry) =>
					entry.label.toLowerCase().includes(normalized) ||
					entry.conceptKey.toLowerCase().includes(normalized) ||
					entry.summary.toLowerCase().includes(normalized),
			)
			.map((entry) => ({
				conceptKey: entry.conceptKey,
				label: entry.label,
				summary: entry.summary,
				masteryPercent: entry.masteryPercent,
				sourceCount: entry.conceptKey === "docker" ? 44 : 3,
			}));

		return Promise.resolve({
			scopeKey: "default",
			query,
			hits,
			total: hits.length,
		});
	}

	getTimeline(): Promise<TimelineResponse> {
		return Promise.resolve({
			scopeKey: "default",
			events: timelineEvents,
		});
	}

	getDiff(resourceType: string, resourceId: string): Promise<KnowledgeDiff> {
		return Promise.resolve(buildDiff(resourceType, resourceId));
	}

	addBookmark(request: AddBookmarkRequest): Promise<KnowledgeBookmark> {
		const bookmark: KnowledgeBookmark = {
			id: `bookmark-${Date.now()}`,
			label: request.label,
			tags: request.tags ?? [],
			conceptKey: request.conceptKey ?? null,
			resourceType: request.resourceType ?? null,
			resourceId: request.resourceId ?? null,
		};

		bookmarks = [...bookmarks, bookmark];

		return Promise.resolve(bookmark);
	}

	addNote(request: AddNoteRequest): Promise<KnowledgeNote> {
		const note: KnowledgeNote = {
			id: `note-${Date.now()}`,
			body: request.body,
			createdAt: new Date().toISOString(),
			conceptKey: request.conceptKey ?? null,
		};

		notes = [...notes, note];

		return Promise.resolve(note);
	}

	removeBookmark(id: string): Promise<void> {
		const exists = bookmarks.some((bookmark) => bookmark.id === id);

		if (!exists) {
			return Promise.reject(new Error("Bookmark not found."));
		}

		bookmarks = bookmarks.filter((bookmark) => bookmark.id !== id);

		return Promise.resolve();
	}

	rebuild(_request?: RebuildWorkspaceRequest): Promise<BrainDashboard> {
		return Promise.resolve(buildDashboard());
	}
}
