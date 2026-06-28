import { useEffect, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { InteractiveMap } from "@/features/map/InteractiveMap";
import { mapService } from "@/services/map/MapService";
import type { HistoricalPlace } from "@/services/map/types";
import styles from "./TimelineMapPanel.module.css";

interface TimelineMapPanelProps {
	artifactId: string;
}

type TimelineMapViewState =
	| { status: "loading" }
	| { status: "ready"; places: HistoricalPlace[] }
	| { status: "empty" }
	| { status: "unavailable" }
	| { status: "error" };

export function TimelineMapPanel({ artifactId }: TimelineMapPanelProps) {
	const [viewState, setViewState] = useState<TimelineMapViewState>({
		status: "loading",
	});

	useEffect(() => {
		let cancelled = false;

		setViewState({ status: "loading" });

		mapService
			.getTimelineMap(artifactId)
			.then((places) => {
				if (cancelled) {
					return;
				}

				if (places === null) {
					setViewState({ status: "unavailable" });
					return;
				}

				if (places.length === 0) {
					setViewState({ status: "empty" });
					return;
				}

				setViewState({ status: "ready", places });
			})
			.catch(() => {
				if (!cancelled) {
					setViewState({ status: "error" });
				}
			});

		return () => {
			cancelled = true;
		};
	}, [artifactId]);

	return (
		<Card className={styles.card}>
			<p className={styles.label}>Historical Map</p>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Loading map" />
				</div>
			) : null}
			{viewState.status === "empty" ? (
				<EmptyState
					className={styles.emptyState}
					title="No places found"
					description="This timeline does not reference any known historical locations yet."
				/>
			) : null}
			{viewState.status === "unavailable" ? (
				<EmptyState
					className={styles.emptyState}
					title="Map unavailable"
					description="Historical map data is not available for this timeline artifact."
				/>
			) : null}
			{viewState.status === "error" ? (
				<EmptyState
					className={styles.emptyState}
					title="Unable to load map"
					description="Something went wrong while loading historical places for this timeline."
				/>
			) : null}
			{viewState.status === "ready" ? (
				<InteractiveMap places={viewState.places} />
			) : null}
		</Card>
	);
}
