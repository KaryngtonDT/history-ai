import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n/useTranslation";
import type { ShadowSpeechLanguage } from "../shadowVoice";
import {
	isSpeechRecognitionSupported,
	resolveRecognitionLanguage,
} from "../shadowVoice";
import styles from "./ShadowVoiceButton.module.css";

interface ShadowVoiceButtonProps {
	onTranscript: (text: string) => void;
	speechLanguage?: ShadowSpeechLanguage;
	targetLanguage?: string;
}

type BrowserSpeechRecognition = {
	lang: string;
	interimResults: boolean;
	maxAlternatives: number;
	onresult:
		| ((event: {
				results: ArrayLike<ArrayLike<{ transcript?: string }>>;
		  }) => void)
		| null;
	onend: (() => void) | null;
	onerror: (() => void) | null;
	start: () => void;
};

type SpeechRecognitionConstructor = new () => BrowserSpeechRecognition;

function getSpeechRecognition(): SpeechRecognitionConstructor | null {
	const globalWindow = window as Window & {
		SpeechRecognition?: SpeechRecognitionConstructor;
		webkitSpeechRecognition?: SpeechRecognitionConstructor;
	};

	return (
		globalWindow.SpeechRecognition ??
		globalWindow.webkitSpeechRecognition ??
		null
	);
}

export function ShadowVoiceButton({
	onTranscript,
	speechLanguage = "auto",
	targetLanguage = "en",
}: ShadowVoiceButtonProps) {
	const { t, locale } = useTranslation();
	const [supported, setSupported] = useState(false);
	const [listening, setListening] = useState(false);

	useEffect(() => {
		setSupported(isSpeechRecognitionSupported());
	}, []);

	if (!supported) {
		return (
			<p className={styles.fallback}>{t("pipeline.shadow.voiceUnavailable")}</p>
		);
	}

	return (
		<button
			type="button"
			className={listening ? styles.active : styles.button}
			onClick={() => {
				const Recognition = getSpeechRecognition();

				if (!Recognition) {
					return;
				}

				const recognition = new Recognition();
				recognition.lang = resolveRecognitionLanguage(
					speechLanguage,
					targetLanguage,
					locale,
				);
				recognition.interimResults = false;
				recognition.maxAlternatives = 1;
				setListening(true);

				recognition.onresult = (event) => {
					const transcript = event.results[0]?.[0]?.transcript?.trim();

					if (transcript) {
						onTranscript(transcript);
					}
				};

				recognition.onend = () => setListening(false);
				recognition.onerror = () => setListening(false);
				recognition.start();
			}}
		>
			{listening
				? t("pipeline.shadow.listening")
				: t("pipeline.shadow.voiceInput")}
		</button>
	);
}

export { speakShadowAnswer } from "../shadowVoice";
