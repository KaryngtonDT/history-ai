import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import styles from "./PageContainer.module.css";

interface PageContainerProps {
	children: ReactNode;
	className?: string;
}

export function PageContainer({ children, className }: PageContainerProps) {
	return <main className={cn(styles.main, className)}>{children}</main>;
}
