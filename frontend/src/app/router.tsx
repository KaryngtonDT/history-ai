import { Route, Routes } from "react-router";
import { AppLayout } from "@/app/AppLayout";
import { CollectionsPage } from "@/pages/Collections/CollectionsPage";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";
import { LibraryItemPage } from "@/pages/Library/LibraryItemPage";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { ProcessingPage } from "@/pages/Processing/ProcessingPage";
import { SettingsPage } from "@/pages/Settings/SettingsPage";
import { VideoTranscriptPage } from "@/pages/VideoTranscript/VideoTranscriptPage";
import { VideoTranslationsPage } from "@/pages/VideoTranslations/VideoTranslationsPage";
import { VideoUploadPage } from "@/pages/VideoUpload/VideoUploadPage";

export function AppRouter() {
	return (
		<Routes>
			<Route element={<AppLayout />}>
				<Route path="/" element={<DashboardPage />} />
				<Route path="/import" element={<ImportPage />} />
				<Route path="/video/upload" element={<VideoUploadPage />} />
				<Route
					path="/video/:videoId/transcript"
					element={<VideoTranscriptPage />}
				/>
				<Route
					path="/video/:videoId/translations"
					element={<VideoTranslationsPage />}
				/>
				<Route path="/library" element={<LibraryPage />} />
				<Route path="/library/:libraryItemId" element={<LibraryItemPage />} />
				<Route path="/collections" element={<CollectionsPage />} />
				<Route path="/processing/:id" element={<ProcessingPage />} />
				<Route path="/settings" element={<SettingsPage />} />
			</Route>
		</Routes>
	);
}
