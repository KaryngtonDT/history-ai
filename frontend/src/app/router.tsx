import { Route, Routes } from "react-router";
import { ProductShell } from "@/features/product";
import { AIEngineSettingsPage } from "@/pages/AIEngineSettings/AIEngineSettingsPage";
import { AudioOverviewPage } from "@/pages/AudioOverview/AudioOverviewPage";
import { AudioTranscriptPage } from "@/pages/AudioTranscript/AudioTranscriptPage";
import { AudioTranslationsPage } from "@/pages/AudioTranslations/AudioTranslationsPage";
import { AudioUploadPage } from "@/pages/AudioUpload/AudioUploadPage";
import { CollectionsPage } from "@/pages/Collections/CollectionsPage";
import { ConnectionsSettingsPage } from "@/pages/ConnectionsSettings/ConnectionsSettingsPage";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";
import { LearningSettingsPage } from "@/pages/LearningSettings/LearningSettingsPage";
import { LibraryItemPage } from "@/pages/Library/LibraryItemPage";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { PipelineSettingsPage } from "@/pages/PipelineSettings/PipelineSettingsPage";
import { ProcessingPage } from "@/pages/Processing/ProcessingPage";
import { RuntimeAnalyticsPage } from "@/pages/RuntimeAnalytics/RuntimeAnalyticsPage";
import { RuntimeSettingsPage } from "@/pages/RuntimeSettings/RuntimeSettingsPage";
import { ServerSettingsPage } from "@/pages/ServerSettings/ServerSettingsPage";
import { SettingsPage } from "@/pages/Settings/SettingsPage";
import { ShadowSettingsPage } from "@/pages/ShadowSettings/ShadowSettingsPage";
import { VideoAudioPage } from "@/pages/VideoAudio/VideoAudioPage";
import { VideoLipSyncPage } from "@/pages/VideoLipSync/VideoLipSyncPage";
import { VideoOverviewPage } from "@/pages/VideoOverview/VideoOverviewPage";
import { VideoRenderPage } from "@/pages/VideoRender/VideoRenderPage";
import { VideoTranscriptPage } from "@/pages/VideoTranscript/VideoTranscriptPage";
import { VideoTranslationsPage } from "@/pages/VideoTranslations/VideoTranslationsPage";
import { VideoUploadPage } from "@/pages/VideoUpload/VideoUploadPage";
import { VideoVoiceClonePage } from "@/pages/VideoVoiceClone/VideoVoiceClonePage";
import { VideoWatchPage } from "@/pages/VideoWatch/VideoWatchPage";
import { WorkspacePage } from "@/pages/Workspace/WorkspacePage";
import { YouTubeImportPage } from "@/pages/YouTubeImport/YouTubeImportPage";

export function AppRouter() {
	return (
		<Routes>
			<Route element={<ProductShell />}>
				<Route path="/" element={<DashboardPage />} />
				<Route path="/import" element={<ImportPage />} />
				<Route path="/video/upload" element={<VideoUploadPage />} />
				<Route path="/youtube/import" element={<YouTubeImportPage />} />
				<Route path="/audio/upload" element={<AudioUploadPage />} />
				<Route path="/audio/:audioId" element={<AudioOverviewPage />} />
				<Route
					path="/audio/:audioId/transcript"
					element={<AudioTranscriptPage />}
				/>
				<Route
					path="/audio/:audioId/translations"
					element={<AudioTranslationsPage />}
				/>
				<Route path="/video/:videoId" element={<VideoOverviewPage />} />
				<Route
					path="/video/:videoId/transcript"
					element={<VideoTranscriptPage />}
				/>
				<Route
					path="/video/:videoId/translations"
					element={<VideoTranslationsPage />}
				/>
				<Route path="/video/:videoId/audio" element={<VideoAudioPage />} />
				<Route
					path="/video/:videoId/voice-clone"
					element={<VideoVoiceClonePage />}
				/>
				<Route path="/video/:videoId/lip-sync" element={<VideoLipSyncPage />} />
				<Route path="/video/:videoId/render" element={<VideoRenderPage />} />
				<Route path="/video/:videoId/watch" element={<VideoWatchPage />} />
				<Route path="/library" element={<LibraryPage />} />
				<Route path="/library/:libraryItemId" element={<LibraryItemPage />} />
				<Route path="/collections" element={<CollectionsPage />} />
				<Route path="/workspace" element={<WorkspacePage />} />
				<Route path="/processing/:id" element={<ProcessingPage />} />
				<Route path="/settings" element={<SettingsPage />} />
				<Route path="/settings/ai" element={<AIEngineSettingsPage />} />
				<Route path="/settings/pipeline" element={<PipelineSettingsPage />} />
				<Route path="/settings/runtime" element={<RuntimeSettingsPage />} />
				<Route
					path="/settings/runtime/analytics"
					element={<RuntimeAnalyticsPage />}
				/>
				<Route path="/settings/learning" element={<LearningSettingsPage />} />
				<Route
					path="/settings/connections"
					element={<ConnectionsSettingsPage />}
				/>
				<Route path="/settings/server" element={<ServerSettingsPage />} />
				<Route path="/settings/shadow" element={<ShadowSettingsPage />} />
				<Route
					path="/settings/shadow/relationship"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/memory"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/teaching"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/knowledge"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/mentor"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/executive"
					element={<ShadowSettingsPage />}
				/>
				<Route path="/settings/shadow/brain" element={<ShadowSettingsPage />} />
				<Route
					path="/settings/shadow/presence"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/browser"
					element={<ShadowSettingsPage />}
				/>
				<Route
					path="/settings/shadow/mobile"
					element={<ShadowSettingsPage />}
				/>
			</Route>
		</Routes>
	);
}
