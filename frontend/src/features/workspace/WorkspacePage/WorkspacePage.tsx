import { useCallback, useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { TeamPanel } from "@/features/collaboration";
import { ExecutionHistoryPanel } from "@/features/history";
import {
	PreferenceProfileCard,
	ReviewPanel,
	ReviewSummary,
} from "@/features/review";
import { reviewService } from "@/services/review/ReviewService";
import type { PreferenceProfile, Review } from "@/services/review/types";
import type { Project } from "@/services/workspace/types";
import { WORKSPACE_TARGET_LANGUAGES } from "@/services/workspace/types";
import { workspaceService } from "@/services/workspace/WorkspaceService";
import { BatchProgress } from "../BatchProgress";
import { ProjectCard } from "../ProjectCard";
import { VideoGrid } from "../VideoGrid";
import styles from "./WorkspacePage.module.css";

const POLL_INTERVAL_MS = 2000;

export function WorkspacePage() {
	const [projects, setProjects] = useState<Project[] | null>(null);
	const [selectedProjectId, setSelectedProjectId] = useState<string | null>(
		null,
	);
	const [selectedProject, setSelectedProject] = useState<Project | null>(null);
	const [selectedLanguages, setSelectedLanguages] = useState<string[]>([
		"fr",
		"de",
	]);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [processing, setProcessing] = useState(false);
	const [creating, setCreating] = useState(false);
	const [newProjectName, setNewProjectName] = useState("");
	const [reviews, setReviews] = useState<Review[]>([]);
	const [preferenceProfile, setPreferenceProfile] =
		useState<PreferenceProfile | null>(null);

	const selectedVideoId = selectedProject?.videos[0]?.videoId ?? null;

	const loadReviewData = useCallback(async (videoId: string | null) => {
		if (!videoId) {
			setReviews([]);
			setPreferenceProfile(null);
			return;
		}

		const [loadedReviews, profile] = await Promise.all([
			reviewService.loadReviews(videoId),
			reviewService.loadPreferenceProfile(),
		]);

		setReviews(reviewService.sortedReviews(loadedReviews));
		setPreferenceProfile(profile);
	}, []);

	const loadProjects = useCallback(() => {
		setProjects(null);
		setLoadError(null);

		void workspaceService
			.listProjects()
			.then((loadedProjects) => {
				setProjects(loadedProjects);
				setSelectedProjectId((currentId) => {
					if (
						currentId &&
						loadedProjects.some((project) => project.id === currentId)
					) {
						return currentId;
					}

					return loadedProjects[0]?.id ?? null;
				});
			})
			.catch(() => {
				setProjects([]);
				setLoadError(
					"Could not reach the server. Check that the backend is running.",
				);
			});
	}, []);

	const refreshSelectedProject = useCallback(async (projectId: string) => {
		const project = await workspaceService.getProject(projectId);
		setSelectedProject(project);
		setProjects(
			(currentProjects) =>
				currentProjects?.map((entry) =>
					entry.id === project.id ? project : entry,
				) ?? null,
		);
	}, []);

	useEffect(() => {
		loadProjects();
	}, [loadProjects]);

	useEffect(() => {
		if (!selectedProjectId) {
			setSelectedProject(null);
			return;
		}

		void refreshSelectedProject(selectedProjectId).catch(() => {
			setSelectedProject(null);
		});
	}, [selectedProjectId, refreshSelectedProject]);

	useEffect(() => {
		if (!selectedProject || !workspaceService.isBatchRunning(selectedProject)) {
			return;
		}

		const timerId = setInterval(() => {
			void refreshSelectedProject(selectedProject.id).catch(() => {});
		}, POLL_INTERVAL_MS);

		return () => {
			clearInterval(timerId);
		};
	}, [selectedProject, refreshSelectedProject]);

	useEffect(() => {
		void loadReviewData(selectedVideoId).catch(() => {
			setReviews([]);
			setPreferenceProfile(null);
		});
	}, [loadReviewData, selectedVideoId]);

	const toggleLanguage = (language: string): void => {
		setSelectedLanguages((current) =>
			current.includes(language)
				? current.filter((entry) => entry !== language)
				: [...current, language],
		);
	};

	const handleCreateProject = (): void => {
		const name = newProjectName.trim();

		if (!name) {
			return;
		}

		setCreating(true);

		void workspaceService
			.createProject({ name })
			.then((project) => {
				setNewProjectName("");
				setSelectedProjectId(project.id);
				loadProjects();
			})
			.finally(() => {
				setCreating(false);
			});
	};

	const handleProcess = (): void => {
		if (!selectedProject) {
			return;
		}

		setProcessing(true);

		void workspaceService
			.processProject(selectedProject.id, {
				targetLanguages: selectedLanguages,
				processingMode: "automatic",
			})
			.then(() => refreshSelectedProject(selectedProject.id))
			.finally(() => {
				setProcessing(false);
			});
	};

	if (projects === null) {
		return (
			<div className={styles.loading}>
				<Spinner label="Loading workspace" />
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState title="Unable to load workspace" description={loadError} />
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<p className={styles.eyebrow}>Workspace</p>
					<h1 className={styles.title}>Project Workspace</h1>
				</div>
				<div className={styles.createForm}>
					<input
						type="text"
						value={newProjectName}
						onChange={(event) => setNewProjectName(event.target.value)}
						placeholder="New project name"
						className={styles.input}
						aria-label="New project name"
					/>
					<button
						type="button"
						className={styles.secondaryButton}
						onClick={handleCreateProject}
						disabled={creating || newProjectName.trim() === ""}
					>
						Create project
					</button>
				</div>
			</header>

			<div className={styles.layout}>
				<section className={styles.sidebar}>
					<h2 className={styles.sectionTitle}>Projects</h2>
					{projects.length === 0 ? (
						<p className={styles.empty}>No projects yet.</p>
					) : (
						<div className={styles.projectList}>
							{projects.map((project) => (
								<ProjectCard
									key={project.id}
									project={project}
									selected={project.id === selectedProjectId}
									onSelect={() => setSelectedProjectId(project.id)}
								/>
							))}
						</div>
					)}
				</section>

				<section className={styles.main}>
					{selectedProject ? (
						<>
							<ProjectCard project={selectedProject} />

							<TeamPanel workspaceId={selectedProject.id} />

							<div className={styles.section}>
								<h2 className={styles.sectionTitle}>Videos</h2>
								<VideoGrid videos={selectedProject.videos} />
							</div>

							<div className={styles.section}>
								<h2 className={styles.sectionTitle}>Languages</h2>
								<ul className={styles.languageList}>
									{WORKSPACE_TARGET_LANGUAGES.map((language) => {
										const selected = selectedLanguages.includes(language);

										return (
											<li key={language}>
												<label className={styles.languageOption}>
													<input
														type="checkbox"
														checked={selected}
														onChange={() => toggleLanguage(language)}
													/>
													<span>
														{selected ? "✓ " : ""}
														{workspaceService.formatLanguage(language)}
													</span>
												</label>
											</li>
										);
									})}
								</ul>
							</div>

							<button
								type="button"
								className={styles.primaryButton}
								onClick={handleProcess}
								disabled={
									processing ||
									!workspaceService.canProcess(
										selectedProject.videos.length,
										selectedLanguages,
									)
								}
							>
								{workspaceService.processButtonLabel(
									selectedProject.videos.length,
								)}
							</button>

							<BatchProgress
								progress={selectedProject.batchProgress}
								status={selectedProject.batchStatus}
								loading={processing}
							/>

							<ExecutionHistoryPanel videoId={selectedVideoId} />

							<ReviewPanel
								key={selectedVideoId ?? "no-video"}
								videoId={selectedVideoId}
								onSaved={() => {
									void loadReviewData(selectedVideoId);
								}}
							/>

							<PreferenceProfileCard profile={preferenceProfile} />

							<div className={styles.section}>
								<h2 className={styles.sectionTitle}>Review History</h2>
								<ReviewSummary reviews={reviews} />
							</div>
						</>
					) : (
						<EmptyState
							title="Select a project"
							description="Create or choose a project to manage videos and batch processing."
						/>
					)}
				</section>
			</div>
		</div>
	);
}
