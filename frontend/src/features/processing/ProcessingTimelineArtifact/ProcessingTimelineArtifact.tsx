import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import type { Timeline } from "@/domain/timeline";
import { ArtifactCardHeader } from "@/features/processing/artifactRenderers/ArtifactCardHeader";
import { InteractiveTimeline } from "@/features/processing/InteractiveTimeline";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingTimelineArtifact.module.css";

interface ProcessingTimelineArtifactProps {
	artifact: Artifact | null;
	contentId?: string;
	isLoading?: boolean;
	structuredTimeline?: Timeline;
}

interface TimelineSection {
	title: string;
	entries: string[];
}

function parseTimelineSections(content: string): TimelineSection[] {
	const sections: TimelineSection[] = [];
	let current: TimelineSection | null = null;

	for (const line of content.split("\n")) {
		if (line.startsWith("# ") && !line.startsWith("## ")) {
			continue;
		}

		if (line.startsWith("## ")) {
			if (current !== null) {
				sections.push(current);
			}

			current = {
				title: line.slice(3).trim(),
				entries: [],
			};
			continue;
		}

		if (line.startsWith("- ")) {
			if (current === null) {
				current = {
					title: "Timeline",
					entries: [],
				};
			}

			current.entries.push(line.slice(2).trim());
		}
	}

	if (current !== null) {
		sections.push(current);
	}

	return sections;
}

function TimelineMarkdownContent({ content }: { content: string }) {
	const sections = parseTimelineSections(content);

	if (sections.length === 0) {
		return <p className={styles.fallbackContent}>{content}</p>;
	}

	return (
		<div className={styles.timelineContent}>
			{sections.map((section) => (
				<section key={section.title} className={styles.section}>
					<h3 className={styles.sectionTitle}>{section.title}</h3>
					{section.entries.length > 0 ? (
						<ul className={styles.entryList}>
							{section.entries.map((entry) => (
								<li key={`${section.title}-${entry}`} className={styles.entry}>
									{entry}
								</li>
							))}
						</ul>
					) : null}
				</section>
			))}
		</div>
	);
}

export function ProcessingTimelineArtifact({
	artifact,
	contentId,
	isLoading = false,
	structuredTimeline,
}: ProcessingTimelineArtifactProps) {
	if (artifact === null) {
		return (
			<Card className={styles.card}>
				<p className={styles.label}>Timeline</p>
				<EmptyState
					className={styles.emptyState}
					title="No timeline yet"
					description="A timeline will appear here once processing generates timeline artifacts."
				/>
			</Card>
		);
	}

	return (
		<Card className={styles.card}>
			{contentId ? (
				<ArtifactCardHeader
					label="Timeline"
					artifact={artifact}
					contentId={contentId}
				/>
			) : (
				<p className={styles.label}>Timeline</p>
			)}
			{isLoading ? (
				<div className={styles.loadingState}>
					<Spinner label="Loading timeline" />
				</div>
			) : structuredTimeline ? (
				<InteractiveTimeline timeline={structuredTimeline} />
			) : (
				<TimelineMarkdownContent content={artifact.content} />
			)}
		</Card>
	);
}
