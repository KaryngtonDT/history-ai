import { Button } from "@/components/ui/Button";
import { useTranslation } from "@/i18n";
import styles from "./CollectionHeader.module.css";

interface CollectionHeaderProps {
	onCreateClick: () => void;
}

export function CollectionHeader({ onCreateClick }: CollectionHeaderProps) {
	const { t } = useTranslation();

	return (
		<header className={styles.header}>
			<div className={styles.text}>
				<h2 className={styles.title}>
					{t("workspace.collections.headerTitle")}
				</h2>
				<p className={styles.description}>
					{t("workspace.collections.headerDescription")}
				</p>
			</div>
			<Button type="button" onClick={onCreateClick}>
				{t("workspace.collections.createCollection")}
			</Button>
		</header>
	);
}
