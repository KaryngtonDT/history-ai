import type { ReactNode } from "react";
import { cn } from "@/lib/cn";

interface PageContainerProps {
	children: ReactNode;
	className?: string;
}

export function PageContainer({ children, className }: PageContainerProps) {
	return (
		<main className={cn("flex-1 px-6 py-8 lg:px-8", className)}>
			{children}
		</main>
	);
}
