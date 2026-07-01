import { Link, NavLink } from "react-router";
import { cn } from "@/lib/cn";
import { NAV_ICONS, RESULTS_EMPTY_HINTS } from "../navIcons";
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
							const icon = NAV_ICONS[item.id] ?? "";

							if (disabled) {
								const hint = RESULTS_EMPTY_HINTS[item.id];

								return (
									<div key={item.id} className={styles.emptyItem}>
										<span className={styles.emptyLabel}>
											{icon ? `${icon} ` : ""}
											{item.label}
										</span>
										{hint ? (
											<>
												<p className={styles.emptyReason}>{hint.reason}</p>
												<Link
													to={hint.actionRoute}
													className={styles.emptyAction}
												>
													{hint.action} →
												</Link>
											</>
										) : null}
									</div>
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
									{icon ? `${icon} ` : ""}
									{item.label}
								</NavLink>
							);
						})}
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
