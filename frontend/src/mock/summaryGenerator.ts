const SENTENCE_PATTERN = /[^.!?]+[.!?]+|[^.!?]+$/g;

const DEFAULT_MAX_SENTENCES = 3;

export function generateSummaryFromTranscript(
	transcript: string,
	maxSentences = DEFAULT_MAX_SENTENCES,
): string {
	const sentences = splitMeaningfulSentences(transcript);

	if (sentences.length === 0) {
		throw new Error("Transcript is empty; cannot generate summary.");
	}

	return sentences.slice(0, maxSentences).join(" ");
}

function splitMeaningfulSentences(transcript: string): string[] {
	const normalized = transcript.trim();

	if (!normalized) {
		return [];
	}

	return [...normalized.matchAll(SENTENCE_PATTERN)]
		.map((match) => match[0].trim())
		.filter(Boolean);
}
