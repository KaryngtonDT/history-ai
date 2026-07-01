import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./ShadowVoiceButton.module.css";

interface ShadowVoiceButtonProps {
	onTranscript: (text: string) => void;
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

export function ShadowVoiceButton({ onTranscript }: ShadowVoiceButtonProps) {
	const { t } = useTranslation();
	const [supported, setSupported] = useState(false);
	const [listening, setListening] = useState(false);

	useEffect(() => {
		setSupported(getSpeechRecognition() !== null);
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
				recognition.lang = document.documentElement.lang || "en";
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

export function speakShadowAnswer(text: string): void {
	if (!("speechSynthesis" in window)) {
		return;
	}

	window.speechSynthesis.cancel();
	const utterance = new SpeechSynthesisUtterance(text);
	utterance.lang = document.documentElement.lang || "en";
	window.speechSynthesis.speak(utterance);
}
