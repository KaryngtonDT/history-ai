import { useTranslation } from "@/i18n/useTranslation";
import type { WatchContextSegment } from "@/services/shadow/types";
import type { VideoTranscript } from "@/services/transcript/types";
import styles from "./ShadowTranscriptPanel.module.css";

interface ShadowTranscriptPanelProps {
	transcript: VideoTranscript | null;
	activeSegment: WatchContextSegment | null;
	collapsed?: boolean;
	onToggle?: () => void;
}

function formatTimestamp(seconds: number): string {
	const minutes = Math.floor(seconds / 60);
	const remainder = Math.floor(seconds % 60)
		.toString()
		.padStart(2, "0");

	return `${minutes}:${remainder}`;
}

export function ShadowTranscriptPanel({
	transcript,
	activeSegment,
	collapsed = false,
	onToggle,
}: ShadowTranscriptPanelProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.panel}>
			<button type="button" className={styles.header} onClick={onToggle}>
				{t("pipeline.shadow.transcriptTitle")}
			</button>
			{!collapsed ? (
				<ul className={styles.list}>
					{transcript?.segments.map((segment) => {
						const isActive = activeSegment?.index === segment.index;

						return (
							<li
								key={segment.index}
								className={isActive ? styles.active : styles.item}
							>
								<span className={styles.time}>
									{formatTimestamp(segment.startTime)}
								</span>
								<span>{segment.text}</span>
							</li>
						);
					})}
				</ul>
			) : null}
		</section>
	);
}
