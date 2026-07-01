import { useTranslation } from "@/i18n/useTranslation";
import styles from "./ShadowControls.module.css";

interface ShadowControlsProps {
	playbackState: "playing" | "paused" | "ended";
	onPause: () => void;
	onResume: () => void;
	isBusy: boolean;
}

export function ShadowControls({
	playbackState,
	onPause,
	onResume,
	isBusy,
}: ShadowControlsProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.controls}>
			{playbackState === "playing" ? (
				<button type="button" onClick={onPause} disabled={isBusy}>
					{t("pipeline.shadow.pause")}
				</button>
			) : (
				<button type="button" onClick={onResume} disabled={isBusy}>
					{t("pipeline.shadow.resume")}
				</button>
			)}
		</div>
	);
}
