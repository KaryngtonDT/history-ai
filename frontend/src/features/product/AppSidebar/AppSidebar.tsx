import { NavLink } from "react-router";
import { cn } from "@/lib/cn";
import { PRODUCT_NAV_GROUPS, resolveNavPath } from "../navigation";
import { useProductContext } from "../ProductContext";
import styles from "./AppSidebar.module.css";

export function AppSidebar() {
	const { videoId } = useProductContext();

	return (
		<aside className={styles.root} aria-label="Product navigation">
			<div className={styles.brand}>
				<p className={styles.brandText}>History AI</p>
				<p className={styles.brandSub}>Guided video localization</p>
			</div>

			<nav className={styles.nav}>
				{PRODUCT_NAV_GROUPS.map((group) => (
					<div key={group.id} className={styles.group}>
						<p className={styles.groupLabel}>{group.label}</p>
						{group.items.map((item) => {
							const path = resolveNavPath(item.to, videoId);
							const disabled = item.requiresVideoId && !videoId;

							if (disabled) {
								return (
									<span
										key={item.id}
										className={cn(styles.link, styles.linkDisabled)}
										title="Upload or select a video first"
									>
										{item.label}
									</span>
								);
							}

							return (
								<NavLink
									key={item.id}
									to={path}
									end={item.end}
									className={({ isActive }) =>
										cn(styles.link, isActive && styles.linkActive)
									}
									title={item.description}
								>
									{item.label}
								</NavLink>
							);
						})}
						{group.id === "results" && !videoId ? (
							<p className={styles.hint}>
								Upload a video to unlock pipeline results.
							</p>
						) : null}
					</div>
				))}
			</nav>

			<div className={styles.footer}>
				<p className={styles.shortcut}>
					Press <kbd>Ctrl</kbd>+<kbd>K</kbd> to search anywhere
				</p>
			</div>
		</aside>
	);
}
