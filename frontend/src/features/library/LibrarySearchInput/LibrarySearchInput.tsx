import styles from "./LibrarySearchInput.module.css";

interface LibrarySearchInputProps {
	value: string;
	onChange: (value: string) => void;
}

export function LibrarySearchInput({
	value,
	onChange,
}: LibrarySearchInputProps) {
	return (
		<label className={styles.field}>
			<span className={styles.label}>Search library</span>
			<input
				type="search"
				className={styles.input}
				value={value}
				onChange={(event) => onChange(event.target.value)}
				placeholder="Search by title…"
				aria-label="Search library"
			/>
		</label>
	);
}
