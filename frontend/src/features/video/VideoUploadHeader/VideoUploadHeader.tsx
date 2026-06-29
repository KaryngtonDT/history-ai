import styles from "./VideoUploadHeader.module.css";

export function VideoUploadHeader() {
	return (
		<header className={styles.header}>
			<h2 className={styles.title}>Upload Video</h2>
			<p className={styles.description}>
				Upload a video file to start localization. Supported formats: MP4, MOV,
				and MKV.
			</p>
		</header>
	);
}
