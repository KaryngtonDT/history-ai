import type { HTMLAttributes, ReactNode } from "react";
import { cn } from "@/lib/cn";
import styles from "./Badge.module.css";

export type BadgeVariant =
	| "success"
	| "warning"
	| "danger"
	| "info"
	| "neutral";

export interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
	variant?: BadgeVariant;
	children: ReactNode;
}

export function Badge({
	variant = "neutral",
	children,
	className,
	...props
}: BadgeProps) {
	return (
		<span
			role="status"
			className={cn(styles.badge, styles[variant], className)}
			{...props}
		>
			{children}
		</span>
	);
}
