import { useEffect, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Dialog } from "@/components/ui/Dialog";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import { collectionService } from "@/services/collection/CollectionService";
import { CollectionAssignmentConflictError } from "@/services/collection/MockCollectionRepository";
import type { Collection } from "@/services/collection/types";
import { ApiError } from "@/shared/errors";
import styles from "./AssignToCollectionDialog.module.css";

type AssignmentState =
	| { status: "idle" }
	| { status: "loading-collections" }
	| { status: "ready" }
	| { status: "assigning" }
	| { status: "success" }
	| { status: "duplicate" }
	| { status: "error"; message: string };

interface AssignToCollectionDialogProps {
	open: boolean;
	onClose: () => void;
	libraryItemId: string;
}

function isDuplicateAssignmentError(error: unknown): boolean {
	if (error instanceof CollectionAssignmentConflictError) {
		return true;
	}

	return error instanceof ApiError && error.status === 409;
}

export function AssignToCollectionDialog({
	open,
	onClose,
	libraryItemId,
}: AssignToCollectionDialogProps) {
	const { t } = useTranslation();
	const [collections, setCollections] = useState<Collection[]>([]);
	const [selectedCollectionId, setSelectedCollectionId] = useState("");
	const [state, setState] = useState<AssignmentState>({ status: "idle" });

	useEffect(() => {
		if (!open) {
			setCollections([]);
			setSelectedCollectionId("");
			setState({ status: "idle" });
			return;
		}

		setState({ status: "loading-collections" });

		void collectionService
			.listCollections()
			.then((loadedCollections) => {
				setCollections(loadedCollections);
				setSelectedCollectionId(loadedCollections[0]?.id ?? "");
				setState({ status: "ready" });
			})
			.catch(() => {
				setState({
					status: "error",
					message: t("workspace.collections.assignDialog.loadCollectionsError"),
				});
			});
	}, [open, t]);

	const handleClose = () => {
		onClose();
	};

	const handleAssign = () => {
		if (!selectedCollectionId) {
			return;
		}

		setState({ status: "assigning" });

		void collectionService
			.assignLibraryItem(selectedCollectionId, libraryItemId)
			.then(() => {
				setState({ status: "success" });
			})
			.catch((error: unknown) => {
				if (isDuplicateAssignmentError(error)) {
					setState({ status: "duplicate" });
					return;
				}

				setState({
					status: "error",
					message: t("workspace.collections.assignDialog.assignError"),
				});
			});
	};

	return (
		<Dialog
			open={open}
			onClose={handleClose}
			title={t("workspace.collections.assignDialog.title")}
			description={t("workspace.collections.assignDialog.description")}
		>
			{state.status === "loading-collections" ? (
				<div className={styles.loading}>
					<Spinner
						label={t("workspace.collections.assignDialog.loadingCollections")}
					/>
				</div>
			) : null}

			{state.status === "ready" || state.status === "assigning" ? (
				<div className={styles.form}>
					{collections.length === 0 ? (
						<EmptyState
							title={t("workspace.collections.noCollectionsYet")}
							description={t("workspace.collections.noCollectionsDescription")}
						/>
					) : (
						<label className={styles.field}>
							<span className={styles.label}>
								{t("workspace.collections.assignDialog.collection")}
							</span>
							<select
								className={styles.select}
								value={selectedCollectionId}
								onChange={(event) =>
									setSelectedCollectionId(event.target.value)
								}
								disabled={state.status === "assigning"}
							>
								{collections.map((collection) => (
									<option key={collection.id} value={collection.id}>
										{collection.name}
									</option>
								))}
							</select>
						</label>
					)}
					<div className={styles.actions}>
						<Button
							type="button"
							variant="secondary"
							onClick={handleClose}
							disabled={state.status === "assigning"}
						>
							{t("common.cancel")}
						</Button>
						<Button
							type="button"
							onClick={handleAssign}
							disabled={
								state.status === "assigning" ||
								collections.length === 0 ||
								selectedCollectionId === ""
							}
						>
							{state.status === "assigning" ? (
								<span className={styles.submitting}>
									<Spinner
										label={t(
											"workspace.collections.assignDialog.assigningCollection",
										)}
									/>
									{t("workspace.collections.assignDialog.assigning")}
								</span>
							) : (
								t("workspace.collections.assignDialog.assign")
							)}
						</Button>
					</div>
				</div>
			) : null}

			{state.status === "success" ? (
				<div className={styles.feedback}>
					<p className={styles.success}>
						{t("workspace.collections.assignDialog.success")}
					</p>
					<Button type="button" onClick={handleClose}>
						{t("workspace.collections.assignDialog.done")}
					</Button>
				</div>
			) : null}

			{state.status === "duplicate" ? (
				<div className={styles.feedback}>
					<p className={styles.warning}>
						{t("workspace.collections.assignDialog.duplicate")}
					</p>
					<Button type="button" variant="secondary" onClick={handleClose}>
						{t("common.close")}
					</Button>
				</div>
			) : null}

			{state.status === "error" ? (
				<div className={styles.feedback}>
					<p className={styles.error}>{state.message}</p>
					<Button type="button" variant="secondary" onClick={handleClose}>
						{t("common.close")}
					</Button>
				</div>
			) : null}
		</Dialog>
	);
}
