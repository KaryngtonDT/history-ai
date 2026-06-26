import { cn } from "@/lib/cn";
import styles from "./Progress.module.css";

export interface ProgressProps {
	value: number;
	className?: string;
}

function clamp(value: number): number {
	return Math.min(100, Math.max(0, value));
}

export function Progress({ value, className }: ProgressProps) {
	const clamped = clamp(value);

	return (
		<svg
			role="progressbar"
			aria-valuenow={clamped}
			aria-valuemin={0}
			aria-valuemax={100}
			viewBox="0 0 100 4"
			preserveAspectRatio="none"
			className={cn(styles.progress, className)}
		>
			<rect width="100" height="4" className={styles.track} rx="2" />
			<rect width={clamped} height="4" className={styles.fill} rx="2" />
		</svg>
	);
}
