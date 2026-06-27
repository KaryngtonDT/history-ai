import { Button } from "@/components/ui/Button";
import styles from "./CollectionHeader.module.css";

interface CollectionHeaderProps {
	onCreateClick: () => void;
}

export function CollectionHeader({ onCreateClick }: CollectionHeaderProps) {
	return (
		<header className={styles.header}>
			<div className={styles.text}>
				<h2 className={styles.title}>Collections</h2>
				<p className={styles.description}>
					Organize library items into themed groups.
				</p>
			</div>
			<Button type="button" onClick={onCreateClick}>
				Create collection
			</Button>
		</header>
	);
}
