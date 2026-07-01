import { type ReactNode, useCallback, useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import {
	AnalyticsDashboard,
	PerformanceCharts,
	ProviderStatistics,
	QualityTrend,
} from "@/features/analytics";
import { ArtifactJourney } from "@/features/artifacts";
import { TeamPanel } from "@/features/collaboration";
import { ExplainThisButton } from "@/features/help";
import { ExecutionHistoryPanel } from "@/features/history";
import { PageIntroduction } from "@/features/product";
import {
	PreferenceProfileCard,
	ReviewPanel,
	ReviewSummary,
} from "@/features/review";
import { useTranslation } from "@/i18n";
import { reviewService } from "@/services/review/ReviewService";
import type { PreferenceProfile, Review } from "@/services/review/types";
import { telemetryService } from "@/services/telemetry/TelemetryService";
import type {
	PipelineTelemetry,
	ProviderStatistics as ProviderStatisticsModel,
	WorkspaceAnalytics,
} from "@/services/telemetry/types";
import type { Project } from "@/services/workspace/types";
import { WORKSPACE_TARGET_LANGUAGES } from "@/services/workspace/types";
import { workspaceService } from "@/services/workspace/WorkspaceService";
import { BatchProgress } from "../BatchProgress";
import { ProjectCard } from "../ProjectCard";
import { VideoGrid } from "../VideoGrid";
import styles from "./WorkspacePage.module.css";

const POLL_INTERVAL_MS = 2000;

const WORKSPACE_TABS = [
	"projects",
	"team",
	"analytics",
	"history",
	"reviews",
	"preferences",
] as const;

type WorkspaceTab = (typeof WORKSPACE_TABS)[number];

export function WorkspacePage() {
	const { t } = useTranslation();
	const [projects, setProjects] = useState<Project[] | null>(null);
	const [selectedProjectId, setSelectedProjectId] = useState<string | null>(
		null,
	);
	const [selectedProject, setSelectedProject] = useState<Project | null>(null);
	const [selectedLanguages, setSelectedLanguages] = useState<string[]>([
		"fr",
		"de",
	]);
	const [activeTab, setActiveTab] = useState<WorkspaceTab>("projects");
	const [loadError, setLoadError] = useState<string | null>(null);
	const [processing, setProcessing] = useState(false);
	const [creating, setCreating] = useState(false);
	const [newProjectName, setNewProjectName] = useState("");
	const [reviews, setReviews] = useState<Review[]>([]);
	const [preferenceProfile, setPreferenceProfile] =
		useState<PreferenceProfile | null>(null);
	const [analytics, setAnalytics] = useState<WorkspaceAnalytics | null>(null);
	const [providerStatistics, setProviderStatistics] =
		useState<ProviderStatisticsModel | null>(null);
	const [telemetryRecords, setTelemetryRecords] = useState<PipelineTelemetry[]>(
		[],
	);
	const [analyticsError, setAnalyticsError] = useState<string | null>(null);
	const [analyticsLoading, setAnalyticsLoading] = useState(false);

	const selectedVideoId = selectedProject?.videos[0]?.videoId ?? null;
	const videoCount = selectedProject?.videos.length ?? 0;

	const loadAnalyticsData = useCallback(
		async (workspaceId: string) => {
			setAnalyticsLoading(true);
			setAnalyticsError(null);

			try {
				const [loadedAnalytics, loadedProviders, loadedTelemetry] =
					await Promise.all([
						telemetryService.loadAnalytics(workspaceId),
						telemetryService.loadProviderStatistics(workspaceId),
						telemetryService.loadTelemetry(workspaceId),
					]);

				setAnalytics(loadedAnalytics);
				setProviderStatistics(loadedProviders);
				setTelemetryRecords(loadedTelemetry);
			} catch {
				setAnalytics(null);
				setProviderStatistics(null);
				setTelemetryRecords([]);
				setAnalyticsError(t("workspace.page.backendUnavailable"));
			} finally {
				setAnalyticsLoading(false);
			}
		},
		[t],
	);

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
				setLoadError(t("workspace.page.backendUnavailable"));
			});
	}, [t]);

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
		if (!selectedProjectId || activeTab !== "analytics") {
			return;
		}

		void loadAnalyticsData(selectedProjectId);
	}, [activeTab, loadAnalyticsData, selectedProjectId]);

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
		if (activeTab !== "reviews" && activeTab !== "preferences") {
			return;
		}

		void loadReviewData(selectedVideoId).catch(() => {
			setReviews([]);
			setPreferenceProfile(null);
		});
	}, [activeTab, loadReviewData, selectedVideoId]);

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

	const processButtonLabel = workspaceService.canProcess(
		selectedProject?.videos.length ?? 0,
		selectedLanguages,
	)
		? videoCount === 1
			? t("workspace.batch.processButtonOne", { count: videoCount })
			: t("workspace.batch.processButtonOther", { count: videoCount })
		: selectedProject
			? t("workspace.batch.processButtonOther", {
					count: selectedProject.videos.length,
				})
			: t("workspace.batch.processButtonOther", { count: 0 });

	const languageLabel = (language: string): string => {
		if (["en", "fr", "de"].includes(language)) {
			return t(`language.${language}`);
		}

		return workspaceService.formatLanguage(language);
	};

	const stickyLanguageSummary =
		selectedLanguages.length > 0
			? t("workspace.page.stickyLanguages", {
					languages: selectedLanguages.map(languageLabel).join(", "),
				})
			: t("workspace.page.stickyLanguagesEmpty");

	const stickyVideoSummary =
		videoCount === 1
			? t("workspace.page.stickyVideosOne", { count: videoCount })
			: t("workspace.page.stickyVideosOther", { count: videoCount });

	const tabLabel = (tab: WorkspaceTab): string =>
		t(`workspace.page.tabs.${tab}`);

	if (projects === null) {
		return (
			<div className={styles.loading}>
				<Spinner label={t("workspace.page.loadingWorkspace")} />
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState
				title={t("workspace.page.unableToLoadWorkspace")}
				description={loadError}
			/>
		);
	}

	const renderTabPanel = (): ReactNode => {
		if (!selectedProject) {
			return (
				<EmptyState
					title={t("workspace.page.selectProjectTitle")}
					description={t("workspace.page.selectProjectDescription")}
				/>
			);
		}

		switch (activeTab) {
			case "projects":
				return (
					<>
						<ProjectCard project={selectedProject} />

						<div className={styles.section}>
							<h2 className={styles.sectionTitle}>
								{t("workspace.page.videos")}
							</h2>
							<VideoGrid videos={selectedProject.videos} />
						</div>

						{selectedVideoId ? (
							<ArtifactJourney
								videoId={selectedVideoId}
								title={t("workspace.page.selectedVideoPipeline")}
							/>
						) : null}

						<div className={styles.stickyBar}>
							<div className={styles.stickySummary}>
								<span>{stickyVideoSummary}</span>
								<span>{stickyLanguageSummary}</span>
							</div>

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
													{languageLabel(language)}
												</span>
											</label>
										</li>
									);
								})}
							</ul>

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
								{processButtonLabel}
							</button>

							<BatchProgress
								progress={selectedProject.batchProgress}
								status={selectedProject.batchStatus}
								loading={processing}
							/>
						</div>
					</>
				);
			case "team":
				return <TeamPanel workspaceId={selectedProject.id} />;
			case "analytics":
				return (
					<>
						<AnalyticsDashboard
							analytics={analytics}
							loading={analyticsLoading}
							error={analyticsError}
						/>

						<div className={styles.section}>
							<h2 className={styles.sectionTitle}>
								{t("workspace.page.providerStatistics")}
							</h2>
							<ProviderStatistics statistics={providerStatistics} />
						</div>

						<div className={styles.section}>
							<h2 className={styles.sectionTitle}>
								{t("workspace.page.performance")}
							</h2>
							<PerformanceCharts records={telemetryRecords} />
						</div>

						<div className={styles.section}>
							<h2 className={styles.sectionTitle}>
								{t("workspace.page.qualityTrend")}
							</h2>
							<QualityTrend
								records={telemetryRecords}
								recentErrors={analytics?.recentErrors ?? []}
							/>
						</div>
					</>
				);
			case "history":
				return <ExecutionHistoryPanel videoId={selectedVideoId} />;
			case "reviews":
				return (
					<>
						<ReviewPanel
							key={selectedVideoId ?? "no-video"}
							videoId={selectedVideoId}
							onSaved={() => {
								void loadReviewData(selectedVideoId);
							}}
						/>

						<div className={styles.section}>
							<h2 className={styles.sectionTitle}>
								{t("workspace.page.reviewHistory")}
							</h2>
							<ReviewSummary reviews={reviews} />
						</div>
					</>
				);
			case "preferences":
				return <PreferenceProfileCard profile={preferenceProfile} />;
			default:
				return null;
		}
	};

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("workspace.page.eyebrow")}
				title={t("workspace.page.title")}
				description={t("workspace.page.description")}
				whatCanIDo={t("workspace.page.whatCanIDo")}
				secondaryActions={<ExplainThisButton featureId="workspace" />}
			/>

			<div className={styles.createForm}>
				<input
					type="text"
					value={newProjectName}
					onChange={(event) => setNewProjectName(event.target.value)}
					placeholder={t("workspace.page.newProjectNamePlaceholder")}
					className={styles.input}
					aria-label={t("workspace.page.newProjectNameAria")}
				/>
				<button
					type="button"
					className={styles.secondaryButton}
					onClick={handleCreateProject}
					disabled={creating || newProjectName.trim() === ""}
				>
					{t("workspace.page.createProject")}
				</button>
			</div>

			<div className={styles.layout}>
				<section className={styles.sidebar}>
					<h2 className={styles.sectionTitle}>
						{t("workspace.page.projects")}
					</h2>
					{projects.length === 0 ? (
						<p className={styles.empty}>{t("workspace.page.noProjectsYet")}</p>
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
					<div
						className={styles.tabList}
						role="tablist"
						aria-label={t("workspace.page.tabsAria")}
					>
						{WORKSPACE_TABS.map((tab) => (
							<button
								key={tab}
								type="button"
								role="tab"
								id={`workspace-tab-${tab}`}
								aria-selected={activeTab === tab}
								aria-controls={`workspace-panel-${tab}`}
								className={
									activeTab === tab
										? `${styles.tab} ${styles.tabActive}`
										: styles.tab
								}
								onClick={() => setActiveTab(tab)}
							>
								{tabLabel(tab)}
							</button>
						))}
					</div>

					<div
						className={styles.tabPanel}
						role="tabpanel"
						id={`workspace-panel-${activeTab}`}
						aria-labelledby={`workspace-tab-${activeTab}`}
					>
						{renderTabPanel()}
					</div>
				</section>
			</div>
		</div>
	);
}
