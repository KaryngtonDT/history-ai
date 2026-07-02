import { useCallback, useEffect, useMemo, useState } from "react";
import { speakShadowPreview } from "@/features/shadow/shadowVoice";
import { useTranslation } from "@/i18n";
import { shadowVoiceService } from "@/services/shadowVoice/ShadowVoiceService";
import type {
	ShadowVoiceCollection,
	ShadowVoiceDefinition,
	ShadowVoicePreset,
	VoiceStudioParameters,
} from "@/services/shadowVoice/types";
import styles from "./VoiceStudio.module.css";

const DEFAULT_PARAMETERS: VoiceStudioParameters = {
	speed: 1,
	pitch: 1,
	warmth: 6,
	energy: 6,
	emotion: 5,
	pauses: 5,
	expressiveness: 6,
	humor: "low",
};

export function VoiceStudio() {
	const { t } = useTranslation();
	const [voices, setVoices] = useState<ShadowVoiceDefinition[]>([]);
	const [collections, setCollections] = useState<ShadowVoiceCollection[]>([]);
	const [presets, setPresets] = useState<ShadowVoicePreset[]>([]);
	const [selectedVoiceId, setSelectedVoiceId] = useState("browser-default");
	const [selectedCollectionId, setSelectedCollectionId] = useState<string | null>(
		null,
	);
	const [parameters, setParameters] =
		useState<VoiceStudioParameters>(DEFAULT_PARAMETERS);
	const [error, setError] = useState<string | null>(null);
	const [isPreviewing, setIsPreviewing] = useState(false);

	useEffect(() => {
		void (async () => {
			try {
				const [library, collectionData] = await Promise.all([
					shadowVoiceService.getLibrary(),
					shadowVoiceService.getCollections(),
				]);
				setVoices(library.voices);
				setCollections(collectionData.collections);
				setPresets(collectionData.presets);
			} catch {
				setError(t("pipeline.shadow.voiceStudio.loadFailed"));
			}
		})();
	}, [t]);

	const filteredVoices = useMemo(() => {
		if (!selectedCollectionId) {
			return voices;
		}

		const collection = collections.find(
			(item) => item.id === selectedCollectionId,
		);

		if (!collection) {
			return voices;
		}

		return voices.filter((voice) => collection.voiceIds.includes(voice.id));
	}, [collections, selectedCollectionId, voices]);

	const selectedVoice = useMemo(
		() => voices.find((voice) => voice.id === selectedVoiceId) ?? voices[0],
		[selectedVoiceId, voices],
	);

	const updateParameter = useCallback(
		<K extends keyof VoiceStudioParameters>(
			key: K,
			value: VoiceStudioParameters[K],
		) => {
			setParameters((current) => ({ ...current, [key]: value }));
		},
		[],
	);

	const handlePreset = useCallback(
		async (presetId: string) => {
			try {
				const result = await shadowVoiceService.applyPreset(presetId);
				setSelectedVoiceId(result.voiceProfile.voiceId);
				setParameters({
					speed: result.voiceProfile.speed,
					pitch: result.voiceProfile.pitch,
					warmth: result.voiceProfile.warmth,
					energy: result.voiceProfile.energy,
					emotion: result.voiceProfile.emotion,
					pauses: result.voiceProfile.pauses,
					expressiveness: result.voiceProfile.expressiveness,
					humor: result.voiceProfile.humor,
				});
			} catch {
				setError(t("pipeline.shadow.voiceStudio.presetFailed"));
			}
		},
		[t],
	);

	const handlePreview = useCallback(async () => {
		if (!selectedVoice) {
			return;
		}

		setIsPreviewing(true);
		setError(null);

		try {
			const preview = await shadowVoiceService.preview({
				voiceId: selectedVoice.id,
				parameters,
			});
			speakShadowPreview(
				preview.text,
				preview.language,
				preview.parameters.speed,
			);
		} catch {
			setError(t("pipeline.shadow.voiceStudio.previewFailed"));
		} finally {
			setIsPreviewing(false);
		}
	}, [parameters, selectedVoice, t]);

	return (
		<div className={styles.root}>
			<section className={styles.card}>
				<h2 className={styles.title}>
					{t("pipeline.shadow.voiceStudio.title")}
				</h2>
				<p className={styles.description}>
					{t("pipeline.shadow.voiceStudio.description")}
				</p>

				<div className={styles.presetList}>
					{presets.map((preset) => (
						<button
							key={preset.id}
							type="button"
							className={styles.presetButton}
							onClick={() => void handlePreset(preset.id)}
						>
							{preset.label}
						</button>
					))}
				</div>
			</section>

			<section className={styles.card}>
				<h3 className={styles.title}>
					{t("pipeline.shadow.voiceStudio.collectionsTitle")}
				</h3>
				<div className={styles.collectionList}>
					<button
						type="button"
						className={`${styles.collectionCard} ${selectedCollectionId === null ? styles.collectionCardActive : ""}`}
						onClick={() => setSelectedCollectionId(null)}
					>
						{t("pipeline.shadow.voiceStudio.allCollections")}
					</button>
					{collections.map((collection) => (
						<button
							key={collection.id}
							type="button"
							className={`${styles.collectionCard} ${selectedCollectionId === collection.id ? styles.collectionCardActive : ""}`}
							onClick={() => setSelectedCollectionId(collection.id)}
						>
							<strong>{collection.label}</strong>
							<p className={styles.meta}>{collection.description}</p>
						</button>
					))}
				</div>
			</section>

			<section className={styles.card}>
				<div className={styles.grid}>
					<label className={styles.field}>
						<span className={styles.label}>
							{t("pipeline.shadow.voiceStudio.voiceLabel")}
						</span>
						<select
							className={styles.select}
							value={selectedVoice?.id ?? ""}
							onChange={(event) => setSelectedVoiceId(event.target.value)}
						>
							{filteredVoices.map((voice) => (
								<option key={voice.id} value={voice.id}>
									{voice.name} ({voice.collectionLabel})
								</option>
							))}
						</select>
					</label>

					<label className={styles.field}>
						<span className={styles.label}>
							{t("pipeline.shadow.voiceStudio.speedLabel")} ({parameters.speed})
						</span>
						<input
							type="range"
							min="0.5"
							max="1.5"
							step="0.05"
							value={parameters.speed}
							onChange={(event) =>
								updateParameter("speed", Number(event.target.value))
							}
						/>
					</label>

					<label className={styles.field}>
						<span className={styles.label}>
							{t("pipeline.shadow.voiceStudio.warmthLabel")} ({parameters.warmth})
						</span>
						<input
							type="range"
							min="0"
							max="10"
							step="1"
							value={parameters.warmth}
							onChange={(event) =>
								updateParameter("warmth", Number(event.target.value))
							}
						/>
					</label>

					<label className={styles.field}>
						<span className={styles.label}>
							{t("pipeline.shadow.voiceStudio.energyLabel")} ({parameters.energy})
						</span>
						<input
							type="range"
							min="0"
							max="10"
							step="1"
							value={parameters.energy}
							onChange={(event) =>
								updateParameter("energy", Number(event.target.value))
							}
						/>
					</label>
				</div>

				{selectedVoice ? (
					<p className={styles.meta}>
						{selectedVoice.engineLabel} · {selectedVoice.accent} ·{" "}
						{selectedVoice.quality}
					</p>
				) : null}

				<div className={styles.actions}>
					<button
						type="button"
						className={styles.previewButton}
						onClick={() => void handlePreview()}
						disabled={isPreviewing || !selectedVoice}
					>
						{t("pipeline.shadow.voiceStudio.previewButton")}
					</button>
				</div>

				{error ? <p className={styles.error}>{error}</p> : null}
			</section>
		</div>
	);
}
