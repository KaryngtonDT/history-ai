import type { HTMLAttributes, ReactNode } from "react";
import { cn } from "@/lib/cn";
import styles from "./Card.module.css";

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
	children: ReactNode;
}

export function Card({ children, className, ...props }: CardProps) {
	return (
		<div className={cn(styles.card, className)} {...props}>
			{children}
		</div>
	);
}
