import { Link, useLocation, useParams } from "react-router";
import { useTranslation } from "@/i18n";
import styles from "./ProductBreadcrumbs.module.css";

const SEGMENT_KEYS: Record<string, string> = {
	import: "shell.breadcrumbs.import",
	video: "shell.breadcrumbs.video",
	upload: "shell.breadcrumbs.upload",
	transcript: "shell.breadcrumbs.transcript",
	translations: "shell.breadcrumbs.translations",
	audio: "shell.breadcrumbs.audio",
	youtube: "shell.breadcrumbs.youtube",
	"voice-clone": "shell.breadcrumbs.voice-clone",
	"lip-sync": "shell.breadcrumbs.lip-sync",
	render: "shell.breadcrumbs.render",
	watch: "shell.breadcrumbs.watch",
	workspace: "shell.breadcrumbs.workspace",
	library: "shell.breadcrumbs.library",
	collections: "shell.breadcrumbs.collections",
	settings: "shell.breadcrumbs.settings",
	ai: "shell.breadcrumbs.ai",
	pipeline: "shell.breadcrumbs.pipeline",
	processing: "shell.breadcrumbs.processing",
};

export function ProductBreadcrumbs() {
	const location = useLocation();
	const params = useParams();
	const { t } = useTranslation();
	const segments = location.pathname.split("/").filter(Boolean);

	const crumbs = segments.map((segment, index) => {
		const path = `/${segments.slice(0, index + 1).join("/")}`;
		const isVideoId = segment === params.videoId;
		const label = isVideoId
			? segments[index + 1]
				? t("shell.breadcrumbs.videoId", { id: segment.slice(0, 8) })
				: t("shell.breadcrumbs.overview")
			: SEGMENT_KEYS[segment]
				? t(SEGMENT_KEYS[segment])
				: segment;

		return { path, label, isLast: index === segments.length - 1 };
	});

	if (crumbs.length === 0) {
		return null;
	}

	return (
		<nav className={styles.root} aria-label={t("shell.breadcrumbs.ariaLabel")}>
			<ol className={styles.list}>
				<li className={styles.item}>
					<Link to="/" className={styles.link}>
						{t("shell.breadcrumbs.home")}
					</Link>
					<span className={styles.separator} aria-hidden="true">
						/
					</span>
				</li>
				{crumbs.map((crumb) => (
					<li key={crumb.path} className={styles.item}>
						{crumb.isLast ? (
							<span className={styles.current} aria-current="page">
								{crumb.label}
							</span>
						) : (
							<Link to={crumb.path} className={styles.link}>
								{crumb.label}
							</Link>
						)}
						{!crumb.isLast ? (
							<span className={styles.separator} aria-hidden="true">
								/
							</span>
						) : null}
					</li>
				))}
			</ol>
		</nav>
	);
}
