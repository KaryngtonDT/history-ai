import { useTranslation } from "@/i18n";
import type { Project } from "@/services/workspace/types";
import styles from "./ProjectCard.module.css";

interface ProjectCardProps {
	project: Project;
	selected?: boolean;
	onSelect?: () => void;
}

export function ProjectCard({
	project,
	selected = false,
	onSelect,
}: ProjectCardProps) {
	const { t } = useTranslation();
	const videoCountLabel =
		project.videos.length === 1
			? t("workspace.projectCard.videoCountOne", {
					count: project.videos.length,
				})
			: t("workspace.projectCard.videoCountOther", {
					count: project.videos.length,
				});

	const content = (
		<>
			<h3 className={styles.title}>{project.name}</h3>
			<p className={styles.meta}>{videoCountLabel}</p>
		</>
	);

	if (!onSelect) {
		return <article className={styles.card}>{content}</article>;
	}

	return (
		<button
			type="button"
			className={selected ? `${styles.card} ${styles.selected}` : styles.card}
			onClick={onSelect}
		>
			{content}
		</button>
	);
}
