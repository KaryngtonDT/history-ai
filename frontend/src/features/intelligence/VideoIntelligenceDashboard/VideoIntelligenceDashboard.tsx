import {
	EMOTION_LABELS,
	LIGHTING_LABELS,
	LIP_VISIBILITY_LABELS,
	MUSIC_LABELS,
	NOISE_LABELS,
	SCENE_LABELS,
	SPEECH_SPEED_LABELS,
	type VideoIntelligence,
} from "@/services/intelligence/types";
import { videoIntelligenceService } from "@/services/intelligence/VideoIntelligenceService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import { QualityIndicators } from "../QualityIndicators";
import { RecommendationReasons } from "../RecommendationReasons";
import { SpeakerOverview } from "../SpeakerOverview";
import styles from "./VideoIntelligenceDashboard.module.css";

interface VideoIntelligenceDashboardProps {
	intelligence: VideoIntelligence | null;
	recommendation: PipelineRecommendation | null;
	loading?: boolean;
}

export function VideoIntelligenceDashboard({
	intelligence,
	recommendation,
	loading = false,
}: VideoIntelligenceDashboardProps) {
	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>Analyzing video intelligence...</p>
			</div>
		);
	}

	if (!intelligence) {
		return null;
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>Video Intelligence</p>
				<span className={styles.badge}>AI Director</span>
			</div>

			<div className={styles.grid}>
				<div>
					<p className={styles.label}>Language</p>
					<p className={styles.value}>{intelligence.audio.language}</p>
				</div>
				<div>
					<p className={styles.label}>Duration</p>
					<p className={styles.value}>
						{videoIntelligenceService.formatDuration(
							intelligence.durationSeconds,
						)}
					</p>
				</div>
				<div>
					<p className={styles.label}>Scene</p>
					<p className={styles.value}>
						{SCENE_LABELS[intelligence.scene] ?? intelligence.scene}
					</p>
				</div>
				<div>
					<p className={styles.label}>Background noise</p>
					<p className={styles.value}>
						{NOISE_LABELS[intelligence.audio.backgroundNoise] ??
							intelligence.audio.backgroundNoise}
					</p>
				</div>
				<div>
					<p className={styles.label}>Music</p>
					<p className={styles.value}>
						{MUSIC_LABELS[intelligence.audio.backgroundMusic] ??
							intelligence.audio.backgroundMusic}
					</p>
				</div>
				<div>
					<p className={styles.label}>Speech speed</p>
					<p className={styles.value}>
						{SPEECH_SPEED_LABELS[intelligence.audio.speechSpeed] ??
							intelligence.audio.speechSpeed}
					</p>
				</div>
				<div>
					<p className={styles.label}>Lighting</p>
					<p className={styles.value}>
						{LIGHTING_LABELS[intelligence.visual.lighting] ??
							intelligence.visual.lighting}
					</p>
				</div>
				<div>
					<p className={styles.label}>Lip visibility</p>
					<p className={styles.value}>
						{LIP_VISIBILITY_LABELS[intelligence.visual.lipVisibility] ??
							intelligence.visual.lipVisibility}
					</p>
				</div>
				<div>
					<p className={styles.label}>Emotion</p>
					<p className={styles.value}>
						{EMOTION_LABELS[intelligence.speech.dominantEmotion] ??
							intelligence.speech.dominantEmotion}
					</p>
				</div>
			</div>

			<SpeakerOverview intelligence={intelligence} />

			<QualityIndicators
				recommendation={recommendation}
				confidence={intelligence.audio.confidence}
			/>

			<RecommendationReasons reasons={recommendation?.reasons ?? []} />

			{recommendation ? (
				<p className={styles.summary}>
					{videoIntelligenceService.formatRecommendationSummary(recommendation)}
				</p>
			) : null}
		</div>
	);
}
