import type { FormEvent, KeyboardEvent } from "react";
import { Button } from "@/components/ui/Button";
import {
	CHAT_INPUT_LABEL,
	CHAT_INPUT_PLACEHOLDER,
	CHAT_SEND_BUTTON_LABEL,
} from "../chatLabels";
import styles from "./ChatInput.module.css";

export interface ChatInputProps {
	value: string;
	onChange: (value: string) => void;
	onSubmit: () => void;
	disabled?: boolean;
	loading?: boolean;
}

export function ChatInput({
	value,
	onChange,
	onSubmit,
	disabled = false,
	loading = false,
}: ChatInputProps) {
	const trimmedValue = value.trim();
	const canSubmit = trimmedValue.length > 0 && !disabled && !loading;

	function handleSubmit(event: FormEvent<HTMLFormElement>): void {
		event.preventDefault();

		if (!canSubmit) {
			return;
		}

		onSubmit();
	}

	function handleKeyDown(event: KeyboardEvent<HTMLTextAreaElement>): void {
		if (event.key === "Enter" && !event.shiftKey) {
			event.preventDefault();

			if (canSubmit) {
				onSubmit();
			}
		}
	}

	return (
		<form className={styles.chatInputForm} onSubmit={handleSubmit}>
			<label className={styles.inputField}>
				<span className={styles.inputLabel}>{CHAT_INPUT_LABEL}</span>
				<textarea
					className={styles.textarea}
					value={value}
					onChange={(event) => onChange(event.target.value)}
					onKeyDown={handleKeyDown}
					placeholder={CHAT_INPUT_PLACEHOLDER}
					rows={3}
					disabled={disabled || loading}
				/>
			</label>
			<Button type="submit" disabled={!canSubmit}>
				{CHAT_SEND_BUTTON_LABEL}
			</Button>
		</form>
	);
}
