import { NavLink } from "react-router";
import { cn } from "@/lib/cn";
import styles from "./Sidebar.module.css";

const navItems = [
	{ to: "/", label: "Dashboard", end: true },
	{ to: "/import", label: "Import", end: false },
	{ to: "/library", label: "Library", end: false },
	{ to: "/collections", label: "Collections", end: false },
	{ to: "/settings", label: "Settings", end: false },
];

export function Sidebar() {
	return (
		<aside className={styles.aside}>
			<div className={styles.brand}>
				<p className={styles.brandText}>🏛 History AI</p>
			</div>
			<nav className={styles.nav}>
				{navItems.map(({ to, label, end }) => (
					<NavLink
						key={to}
						to={to}
						end={end}
						className={({ isActive }) =>
							cn(styles.link, isActive && styles.linkActive)
						}
					>
						{label}
					</NavLink>
				))}
			</nav>
		</aside>
	);
}
