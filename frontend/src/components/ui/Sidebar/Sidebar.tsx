import { NavLink } from "react-router";
import { cn } from "@/lib/cn";

const navItems = [
	{ to: "/", label: "Dashboard", end: true },
	{ to: "/import", label: "Import", end: false },
	{ to: "/library", label: "Library", end: false },
	{ to: "/settings", label: "Settings", end: false },
];

export function Sidebar() {
	return (
		<aside className="flex w-full flex-col border-b border-[var(--color-border-default)] bg-[var(--color-bg-elevated)] lg:w-[var(--space-sidebar)] lg:border-r lg:border-b-0">
			<div className="border-b border-[var(--color-border-default)] px-6 py-5">
				<p className="text-base font-semibold text-[var(--color-text-primary)]">
					🏛 History AI
				</p>
			</div>
			<nav className="flex flex-1 flex-row gap-1 overflow-x-auto p-3 lg:flex-col lg:gap-0 lg:p-4">
				{navItems.map(({ to, label, end }) => (
					<NavLink
						key={to}
						to={to}
						end={end}
						className={({ isActive }) =>
							cn(
								"whitespace-nowrap rounded-[var(--radius-md)] px-3 py-2.5 text-sm font-medium transition-colors duration-200",
								isActive
									? "bg-[var(--color-accent-subtle)] text-[var(--color-accent-default)]"
									: "text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-subtle)] hover:text-[var(--color-text-primary)]",
							)
						}
					>
						{label}
					</NavLink>
				))}
			</nav>
		</aside>
	);
}
