import { cn } from "@/lib/cn";
import styles from "./Spinner.module.css";

export interface SpinnerProps {
	className?: string;
	label?: string;
}

export function Spinner({ className, label = "Loading" }: SpinnerProps) {
	return (
		<div
			role="status"
			aria-label={label}
			className={cn(styles.spinner, className)}
		/>
	);
}
