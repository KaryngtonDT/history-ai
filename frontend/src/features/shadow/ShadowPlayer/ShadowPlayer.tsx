import type { RefObject } from "react";
import styles from "./ShadowPlayer.module.css";

interface ShadowPlayerProps {
	streamUrl: string | null;
	videoRef: RefObject<HTMLVideoElement | null>;
	onTimeUpdate: (time: number) => void;
}

export function ShadowPlayer({
	streamUrl,
	videoRef,
	onTimeUpdate,
}: ShadowPlayerProps) {
	if (!streamUrl) {
		return null;
	}

	return (
		<video
			ref={videoRef}
			className={styles.video}
			src={streamUrl}
			controls
			preload="metadata"
			onTimeUpdate={(event) => onTimeUpdate(event.currentTarget.currentTime)}
		>
			<track kind="captions" />
		</video>
	);
}
