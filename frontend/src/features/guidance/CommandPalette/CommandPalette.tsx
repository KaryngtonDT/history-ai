import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router";
import { useProductContext } from "@/features/product/ProductContext";
import { videoPipelinePath } from "@/features/product/videoRoutes";
import { useTranslation } from "@/i18n";
import { COMMAND_ITEMS, filterCommandItems } from "../commandItems";
import styles from "./CommandPalette.module.css";

export function CommandPalette() {
	const [open, setOpen] = useState(false);
	const [query, setQuery] = useState("");
	const navigate = useNavigate();
	const { videoId } = useProductContext();
	const { t } = useTranslation();

	const items = useMemo(() => filterCommandItems(query, t), [query, t]);

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
				aria-label={t("guidance.palette.closeAria")}
				onClick={() => setOpen(false)}
			/>
			<div
				className={styles.panel}
				role="dialog"
				aria-label={t("guidance.palette.dialogAria")}
			>
				<input
					className={styles.input}
					type="search"
					placeholder={t("guidance.palette.placeholder")}
					value={query}
					onChange={(event) => setQuery(event.target.value)}
					ref={(element) => element?.focus()}
				/>
				{items.length === 0 ? (
					<p className={styles.empty}>{t("guidance.palette.empty")}</p>
				) : (
					<div className={styles.list}>
						{Object.entries(grouped).map(([group, groupItems]) => (
							<div key={group} className={styles.group}>
								<p className={styles.groupLabel}>
									{t(`guidance.palette.groups.${group}`)}
								</p>
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
												<span className={styles.itemLabel}>
													{t(`guidance.commands.${item.id}.label`)}
												</span>
												<span className={styles.itemDescription}>
													{t(`guidance.commands.${item.id}.description`)}
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
					{t("guidance.palette.footer", { count: COMMAND_ITEMS.length })}
				</p>
			</div>
		</div>
	);
}
