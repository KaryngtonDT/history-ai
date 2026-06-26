import type { ButtonHTMLAttributes, ReactNode } from "react";
import { cn } from "@/lib/cn";
import styles from "./Button.module.css";

export type ButtonVariant = "primary" | "secondary" | "ghost";
export type ButtonSize = "sm" | "md" | "lg";

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
	variant?: ButtonVariant;
	size?: ButtonSize;
	children: ReactNode;
}

export function Button({
	variant = "primary",
	size = "md",
	disabled,
	className,
	children,
	type = "button",
	...props
}: ButtonProps) {
	return (
		<button
			type={type}
			disabled={disabled}
			className={cn(styles.button, styles[variant], styles[size], className)}
			{...props}
		>
			{children}
		</button>
	);
}
