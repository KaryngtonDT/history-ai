import { type ReactNode, useEffect } from "react";
import { cn } from "@/lib/cn";
import styles from "./Dialog.module.css";

export interface DialogProps {
	open: boolean;
	onClose: () => void;
	title: string;
	description?: string;
	children: ReactNode;
	className?: string;
}

export function Dialog({
	open,
	onClose,
	title,
	description,
	children,
	className,
}: DialogProps) {
	useEffect(() => {
		if (!open) {
			return;
		}

		const onKeyDown = (event: KeyboardEvent) => {
			if (event.key === "Escape") {
				onClose();
			}
		};

		document.addEventListener("keydown", onKeyDown);

		return () => {
			document.removeEventListener("keydown", onKeyDown);
		};
	}, [open, onClose]);

	if (!open) {
		return null;
	}

	const titleId = "dialog-title";
	const descriptionId = description ? "dialog-description" : undefined;

	return (
		<div className={styles.overlay}>
			<button
				type="button"
				className={styles.backdrop}
				onClick={onClose}
				aria-label="Close dialog"
			/>
			<div
				role="dialog"
				aria-modal="true"
				aria-labelledby={titleId}
				aria-describedby={descriptionId}
				className={cn(styles.dialog, className)}
				onMouseDown={(event) => event.stopPropagation()}
			>
				<header className={styles.header}>
					<h2 id={titleId} className={styles.title}>
						{title}
					</h2>
					{description ? (
						<p id={descriptionId} className={styles.description}>
							{description}
						</p>
					) : null}
				</header>
				<div className={styles.content}>{children}</div>
			</div>
		</div>
	);
}
