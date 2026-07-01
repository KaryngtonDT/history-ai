import { Link, useLocation, useParams } from "react-router";
import styles from "./ProductBreadcrumbs.module.css";

const LABELS: Record<string, string> = {
	"": "Home",
	import: "Import",
	video: "Video",
	upload: "Upload",
	transcript: "Transcript",
	translations: "Translations",
	audio: "Audio",
	"voice-clone": "Cloned Voice",
	"lip-sync": "Lip Sync Preview",
	render: "Final Video",
	workspace: "Workspace",
	library: "Library",
	collections: "Collections",
	settings: "Settings",
	ai: "AI Models",
	pipeline: "Pipeline Setup",
	processing: "Processing",
};

export function ProductBreadcrumbs() {
	const location = useLocation();
	const params = useParams();
	const segments = location.pathname.split("/").filter(Boolean);

	const crumbs = segments.map((segment, index) => {
		const path = `/${segments.slice(0, index + 1).join("/")}`;
		const isVideoId = segment === params.videoId;
		const label = isVideoId
			? segments[index + 1]
				? `Video ${segment.slice(0, 8)}…`
				: "Overview"
			: (LABELS[segment] ?? segment);

		return { path, label, isLast: index === segments.length - 1 };
	});

	if (crumbs.length === 0) {
		return null;
	}

	return (
		<nav className={styles.root} aria-label="Breadcrumb">
			<ol className={styles.list}>
				<li className={styles.item}>
					<Link to="/" className={styles.link}>
						Home
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
