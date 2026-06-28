import type { CSSProperties } from "react";
import styles from "./InteractiveMap.module.css";

export interface MapPlace {
	name: string;
	coordinates: {
		latitude: number;
		longitude: number;
	};
	description: string | null;
}

interface InteractiveMapProps {
	places: MapPlace[];
}

function formatCoordinate(value: number): string {
	return value.toFixed(4);
}

export function InteractiveMap({ places }: InteractiveMapProps) {
	return (
		<section
			className={styles.root}
			aria-label="Historical places map"
			data-testid="interactive-map"
		>
			<div className={styles.mapCanvas} aria-hidden="true">
				{places.map((place) => (
					<span
						key={place.name}
						className={styles.mapMarker}
						style={
							{
								"--marker-x": `${((place.coordinates.longitude + 180) / 360) * 100}%`,
								"--marker-y": `${((90 - place.coordinates.latitude) / 180) * 100}%`,
							} as CSSProperties
						}
					/>
				))}
			</div>
			<ol className={styles.placeList}>
				{places.map((place) => (
					<li key={place.name} className={styles.placeItem}>
						<article className={styles.placeCard}>
							<h3 className={styles.placeName}>{place.name}</h3>
							<dl className={styles.coordinates}>
								<div className={styles.coordinateRow}>
									<dt>Latitude</dt>
									<dd>{formatCoordinate(place.coordinates.latitude)}</dd>
								</div>
								<div className={styles.coordinateRow}>
									<dt>Longitude</dt>
									<dd>{formatCoordinate(place.coordinates.longitude)}</dd>
								</div>
							</dl>
							{place.description ? (
								<p className={styles.description}>{place.description}</p>
							) : null}
						</article>
					</li>
				))}
			</ol>
		</section>
	);
}
