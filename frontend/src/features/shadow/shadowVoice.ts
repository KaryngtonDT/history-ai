export type ShadowSpeechLanguage = "auto" | "en" | "fr" | "de";

const LANGUAGE_PREFIXES: Record<
	Exclude<ShadowSpeechLanguage, "auto">,
	string[]
> = {
	en: ["en"],
	fr: ["fr"],
	de: ["de"],
};

export function getBcp47ForLanguage(language: string): string {
	switch (language) {
		case "fr":
			return "fr-FR";
		case "de":
			return "de-DE";
		default:
			return "en-US";
	}
}

export function isSpeechSynthesisSupported(): boolean {
	return typeof window !== "undefined" && "speechSynthesis" in window;
}

export function isSpeechRecognitionSupported(): boolean {
	if (typeof window === "undefined") {
		return false;
	}

	const globalWindow = window as Window & {
		SpeechRecognition?: unknown;
		webkitSpeechRecognition?: unknown;
	};

	return Boolean(
		globalWindow.SpeechRecognition ?? globalWindow.webkitSpeechRecognition,
	);
}

export function listBrowserVoices(): SpeechSynthesisVoice[] {
	if (!isSpeechSynthesisSupported()) {
		return [];
	}

	return window.speechSynthesis.getVoices();
}

export function pickBrowserVoice(
	language: string,
): SpeechSynthesisVoice | null {
	const voices = listBrowserVoices();
	const prefixes = LANGUAGE_PREFIXES[
		language as Exclude<ShadowSpeechLanguage, "auto">
	] ?? [language];

	for (const prefix of prefixes) {
		const exact = voices.find((voice) =>
			voice.lang.toLowerCase().startsWith(prefix),
		);

		if (exact) {
			return exact;
		}
	}

	return voices[0] ?? null;
}

export function isVoiceAvailableForLanguage(language: string): boolean {
	return pickBrowserVoice(language) !== null;
}

export function resolveRecognitionLanguage(
	selectedLanguage: ShadowSpeechLanguage,
	targetLanguage: string,
	uiLocale: string,
): string {
	if (selectedLanguage !== "auto") {
		return getBcp47ForLanguage(selectedLanguage);
	}

	const fromTarget = normalizeLanguageCode(targetLanguage);

	if (fromTarget) {
		return getBcp47ForLanguage(fromTarget);
	}

	return getBcp47ForLanguage(uiLocale);
}

export function resolveSpeechLanguageFromAnswer(
	speechLanguage: string | undefined,
): string {
	if (speechLanguage && ["en", "fr", "de"].includes(speechLanguage)) {
		return speechLanguage;
	}

	return "en";
}

function normalizeLanguageCode(value: string): "en" | "fr" | "de" | null {
	const normalized = value.trim().toLowerCase();

	if (["en", "english", "anglais"].includes(normalized)) {
		return "en";
	}

	if (["fr", "french", "francais", "français"].includes(normalized)) {
		return "fr";
	}

	if (["de", "german", "deutsch", "allemand"].includes(normalized)) {
		return "de";
	}

	return null;
}

export function toVoicePreferencePayload(
	selectedLanguage: ShadowSpeechLanguage,
): {
	mode: "same_as_target_language" | "manual";
	manualLanguage?: "en" | "fr" | "de";
} {
	if (selectedLanguage === "auto") {
		return { mode: "same_as_target_language" };
	}

	return {
		mode: "manual",
		manualLanguage: selectedLanguage,
	};
}

export function selectedLanguageFromPreference(preference: {
	mode: string;
	manualLanguage?: string | null;
}): ShadowSpeechLanguage {
	if (preference.mode === "manual" && preference.manualLanguage) {
		const code = preference.manualLanguage as ShadowSpeechLanguage;

		if (code === "en" || code === "fr" || code === "de") {
			return code;
		}
	}

	return "auto";
}

export function effectiveSpeechLanguage(
	selectedLanguage: ShadowSpeechLanguage,
	targetLanguage: string,
): string {
	if (selectedLanguage !== "auto") {
		return selectedLanguage;
	}

	const normalized = targetLanguage.trim().toLowerCase();

	if (["fr", "french", "francais", "français"].includes(normalized)) {
		return "fr";
	}

	if (["de", "german", "deutsch", "allemand"].includes(normalized)) {
		return "de";
	}

	if (["en", "english", "anglais"].includes(normalized)) {
		return "en";
	}

	return "en";
}

export interface SpeakShadowAnswerResult {
	spoken: boolean;
	fallbackUsed: boolean;
}

export function speakShadowAnswer(
	text: string,
	speechLanguage = "en",
	rate = 1,
): SpeakShadowAnswerResult {
	if (!isSpeechSynthesisSupported()) {
		return { spoken: false, fallbackUsed: true };
	}

	window.speechSynthesis.cancel();
	const utterance = new SpeechSynthesisUtterance(text);
	const voice = pickBrowserVoice(speechLanguage);

	utterance.lang = getBcp47ForLanguage(speechLanguage);
	utterance.rate = Math.min(2, Math.max(0.5, rate));

	if (voice) {
		utterance.voice = voice;
	}

	window.speechSynthesis.speak(utterance);

	return {
		spoken: true,
		fallbackUsed: voice === null,
	};
}

export function speakShadowPreview(
	text: string,
	language: string,
	speed = 1,
): SpeakShadowAnswerResult {
	return speakShadowAnswer(text, language, speed);
}
