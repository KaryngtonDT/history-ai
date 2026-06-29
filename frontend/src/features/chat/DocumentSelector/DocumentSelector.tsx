import {
	DOCUMENT_SELECTOR_LABEL,
	DOCUMENT_SELECTOR_TITLE,
} from "../chatLabels";
import styles from "./DocumentSelector.module.css";

export interface AvailableDocument {
	contentId: string;
	label: string;
}

export interface DocumentSelectorProps {
	availableDocuments: AvailableDocument[];
	selectedContentIds: string[];
	onSelectionChange: (contentIds: string[]) => void;
	disabled?: boolean;
}

function buildNextSelection(
	availableDocuments: AvailableDocument[],
	selectedContentIds: string[],
	contentId: string,
	checked: boolean,
): string[] {
	const selected = new Set(selectedContentIds);

	if (checked) {
		selected.add(contentId);
	} else if (selected.size <= 1) {
		return selectedContentIds;
	} else {
		selected.delete(contentId);
	}

	return availableDocuments
		.map((document) => document.contentId)
		.filter((id) => selected.has(id));
}

export function DocumentSelector({
	availableDocuments,
	selectedContentIds,
	onSelectionChange,
	disabled = false,
}: DocumentSelectorProps) {
	const selectedSet = new Set(selectedContentIds);

	return (
		<fieldset
			className={styles.documentSelector}
			disabled={disabled}
			aria-label={DOCUMENT_SELECTOR_LABEL}
		>
			<legend className={styles.legend}>{DOCUMENT_SELECTOR_TITLE}</legend>
			<ul className={styles.documentList}>
				{availableDocuments.map((document) => {
					const isChecked = selectedSet.has(document.contentId);
					const isOnlySelection = isChecked && selectedContentIds.length === 1;

					return (
						<li key={document.contentId}>
							<label className={styles.documentOption}>
								<input
									type="checkbox"
									checked={isChecked}
									disabled={disabled || isOnlySelection}
									onChange={(event) => {
										onSelectionChange(
											buildNextSelection(
												availableDocuments,
												selectedContentIds,
												document.contentId,
												event.target.checked,
											),
										);
									}}
								/>
								<span>{document.label}</span>
							</label>
						</li>
					);
				})}
			</ul>
		</fieldset>
	);
}
