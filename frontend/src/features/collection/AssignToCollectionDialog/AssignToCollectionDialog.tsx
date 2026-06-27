import { useEffect, useState } from "react";
import { Button } from "@/components/ui/Button";
import { Dialog } from "@/components/ui/Dialog";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
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
					message: "Could not load collections. Please try again.",
				});
			});
	}, [open]);

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
					message: "Could not assign the library item. Please try again.",
				});
			});
	};

	return (
		<Dialog
			open={open}
			onClose={handleClose}
			title="Assign to collection"
			description="Choose a collection for this library item."
		>
			{state.status === "loading-collections" ? (
				<div className={styles.loading}>
					<Spinner label="Loading collections" />
				</div>
			) : null}

			{state.status === "ready" || state.status === "assigning" ? (
				<div className={styles.form}>
					{collections.length === 0 ? (
						<EmptyState
							title="No collections yet"
							description="Create a collection first, then assign library items to it."
						/>
					) : (
						<label className={styles.field}>
							<span className={styles.label}>Collection</span>
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
							Cancel
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
									<Spinner label="Assigning to collection" />
									Assigning…
								</span>
							) : (
								"Assign"
							)}
						</Button>
					</div>
				</div>
			) : null}

			{state.status === "success" ? (
				<div className={styles.feedback}>
					<p className={styles.success}>Library item assigned successfully.</p>
					<Button type="button" onClick={handleClose}>
						Done
					</Button>
				</div>
			) : null}

			{state.status === "duplicate" ? (
				<div className={styles.feedback}>
					<p className={styles.warning}>
						This library item is already in the selected collection.
					</p>
					<Button type="button" variant="secondary" onClick={handleClose}>
						Close
					</Button>
				</div>
			) : null}

			{state.status === "error" ? (
				<div className={styles.feedback}>
					<p className={styles.error}>{state.message}</p>
					<Button type="button" variant="secondary" onClick={handleClose}>
						Close
					</Button>
				</div>
			) : null}
		</Dialog>
	);
}
