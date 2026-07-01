import { Link, NavLink } from "react-router";
import { useTranslation } from "@/i18n";
import { cn } from "@/lib/cn";
import { NAV_ICONS } from "../navIcons";
import { PRODUCT_NAV_GROUPS, resolveNavPath } from "../navigation";
import { useProductContext } from "../ProductContext";
import styles from "./AppSidebar.module.css";

function shortcutModifier(): string {
	if (
		typeof navigator !== "undefined" &&
		/Mac|iPhone|iPad/.test(navigator.platform)
	) {
		return "⌘";
	}

	return "Ctrl";
}

export function AppSidebar() {
	const { videoId } = useProductContext();
	const { t } = useTranslation();

	return (
		<aside className={styles.root} aria-label={t("shell.sidebar.ariaLabel")}>
			<div className={styles.brand}>
				<p className={styles.brandText}>{t("shell.brand.title")}</p>
				<p className={styles.brandSub}>{t("shell.brand.tagline")}</p>
			</div>

			<nav className={styles.nav}>
				{PRODUCT_NAV_GROUPS.map((group) => (
					<div key={group.id} className={styles.group}>
						<p className={styles.groupLabel}>
							{t(`shell.nav.groups.${group.id}`)}
						</p>
						{group.items.map((item) => {
							const path = resolveNavPath(item.to, videoId);
							const disabled = item.requiresVideoId && !videoId;
							const icon = NAV_ICONS[item.id] ?? "";
							const label = t(`shell.nav.items.${item.id}.label`);
							const descriptionKey = `shell.nav.items.${item.id}.description`;
							const description = t(descriptionKey);
							const hasDescription = description !== descriptionKey;

							if (disabled) {
								const reason = t(`shell.nav.empty.${item.id}.reason`);
								const action = t(`shell.nav.empty.${item.id}.action`);
								const hasEmptyHint =
									reason !== `shell.nav.empty.${item.id}.reason`;

								return (
									<div key={item.id} className={styles.emptyItem}>
										<span className={styles.emptyLabel}>
											{icon ? `${icon} ` : ""}
											{label}
										</span>
										{hasEmptyHint ? (
											<>
												<p className={styles.emptyReason}>{reason}</p>
												<Link to="/video/upload" className={styles.emptyAction}>
													{action} →
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
									title={hasDescription ? description : undefined}
								>
									{icon ? `${icon} ` : ""}
									{label}
								</NavLink>
							);
						})}
					</div>
				))}
			</nav>

			<div className={styles.footer}>
				<p className={styles.shortcut}>
					{t("shell.sidebar.shortcut", { modifier: shortcutModifier() })}
				</p>
			</div>
		</aside>
	);
}
