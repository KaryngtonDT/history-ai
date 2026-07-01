import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router";
import { useProductContext } from "@/features/product/ProductContext";
import { videoPipelinePath } from "@/features/product/videoRoutes";
import { COMMAND_ITEMS, filterCommandItems } from "../commandItems";
import styles from "./CommandPalette.module.css";

export function CommandPalette() {
	const [open, setOpen] = useState(false);
	const [query, setQuery] = useState("");
	const navigate = useNavigate();
	const { videoId } = useProductContext();

	const items = useMemo(() => filterCommandItems(query), [query]);

	useEffect(() => {
		const onKeyDown = (event: KeyboardEvent) => {
			if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
				event.preventDefault();
				setOpen((current) => !current);
			}

			if (event.key === "Escape") {
				setOpen(false);
			}
		};

		window.addEventListener("keydown", onKeyDown);
		return () => window.removeEventListener("keydown", onKeyDown);
	}, []);

	if (!open) {
		return null;
	}

	const resolvePath = (id: string, fallback: string): string => {
		if (!videoId) {
			return fallback;
		}

		const contextual: Record<string, string> = {
			transcript: videoPipelinePath("transcript", videoId),
			translations: videoPipelinePath("translations", videoId),
			audio: videoPipelinePath("audio", videoId),
			"voice-clone": videoPipelinePath("voice-clone", videoId),
			"lip-sync": videoPipelinePath("lip-sync", videoId),
			render: videoPipelinePath("render", videoId),
		};

		return contextual[id] ?? fallback;
	};

	const grouped = items.reduce<Record<string, typeof items>>((acc, item) => {
		acc[item.group] = acc[item.group] ?? [];
		acc[item.group].push(item);
		return acc;
	}, {});

	return (
		<div className={styles.overlay}>
			<button
				type="button"
				className={styles.backdrop}
				aria-label="Close command palette"
				onClick={() => setOpen(false)}
			/>
			<div className={styles.panel} role="dialog" aria-label="Command palette">
				<input
					className={styles.input}
					type="search"
					placeholder="Search videos, projects, pipeline, analytics…"
					value={query}
					onChange={(event) => setQuery(event.target.value)}
					ref={(element) => element?.focus()}
				/>
				{items.length === 0 ? (
					<p className={styles.empty}>No matching commands.</p>
				) : (
					<div className={styles.list}>
						{Object.entries(grouped).map(([group, groupItems]) => (
							<div key={group} className={styles.group}>
								<p className={styles.groupLabel}>{group}</p>
								<ul className={styles.groupList}>
									{groupItems.map((item) => (
										<li key={item.id}>
											<button
												type="button"
												className={styles.itemButton}
												onClick={() => {
													navigate(resolvePath(item.id, item.path));
													setOpen(false);
													setQuery("");
												}}
											>
												<span className={styles.itemLabel}>{item.label}</span>
												<span className={styles.itemDescription}>
													{item.description}
												</span>
											</button>
										</li>
									))}
								</ul>
							</div>
						))}
					</div>
				)}
				<p className={styles.hint}>
					{COMMAND_ITEMS.length} commands · Esc to close
				</p>
			</div>
		</div>
	);
}
