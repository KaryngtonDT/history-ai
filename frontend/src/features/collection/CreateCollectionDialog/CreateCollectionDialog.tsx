import { type FormEvent, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Dialog } from "@/components/ui/Dialog";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import { collectionService } from "@/services/collection/CollectionService";
import styles from "./CreateCollectionDialog.module.css";

interface CreateCollectionDialogProps {
	open: boolean;
	onClose: () => void;
	onCreated: () => void;
}

export function CreateCollectionDialog({
	open,
	onClose,
	onCreated,
}: CreateCollectionDialogProps) {
	const { t } = useTranslation();
	const [name, setName] = useState("");
	const [description, setDescription] = useState("");
	const [submitting, setSubmitting] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const resetForm = () => {
		setName("");
		setDescription("");
		setSubmitting(false);
		setError(null);
	};

	const handleClose = () => {
		resetForm();
		onClose();
	};

	const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
		event.preventDefault();
		setSubmitting(true);
		setError(null);

		void collectionService
			.createCollection({ name, description })
			.then(() => {
				resetForm();
				onCreated();
				onClose();
			})
			.catch(() => {
				setSubmitting(false);
				setError(t("workspace.collections.createDialog.error"));
			});
	};

	return (
		<Dialog
			open={open}
			onClose={handleClose}
			title={t("workspace.collections.createDialog.title")}
			description={t("workspace.collections.createDialog.description")}
		>
			<form className={styles.form} onSubmit={handleSubmit}>
				<label className={styles.field}>
					<span className={styles.label}>
						{t("workspace.collections.createDialog.name")}
					</span>
					<input
						className={styles.input}
						value={name}
						onChange={(event) => setName(event.target.value)}
						required
						disabled={submitting}
					/>
				</label>
				<label className={styles.field}>
					<span className={styles.label}>
						{t("workspace.collections.createDialog.descriptionLabel")}
					</span>
					<textarea
						className={styles.textarea}
						value={description}
						onChange={(event) => setDescription(event.target.value)}
						rows={3}
						disabled={submitting}
					/>
				</label>
				{error ? <p className={styles.error}>{error}</p> : null}
				<div className={styles.actions}>
					<Button
						type="button"
						variant="secondary"
						onClick={handleClose}
						disabled={submitting}
					>
						{t("common.cancel")}
					</Button>
					<Button type="submit" disabled={submitting || name.trim() === ""}>
						{submitting ? (
							<span className={styles.submitting}>
								<Spinner
									label={t(
										"workspace.collections.createDialog.creatingCollection",
									)}
								/>
								{t("workspace.collections.createDialog.creating")}
							</span>
						) : (
							t("workspace.collections.createDialog.create")
						)}
					</Button>
				</div>
			</form>
		</Dialog>
	);
}
