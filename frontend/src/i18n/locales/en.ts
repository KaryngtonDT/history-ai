import { type DeepStringRecord, mergeMessages } from "../localeUtils";
import { learningDe } from "./sections/learning.de";
import { learningEn } from "./sections/learning.en";
import { learningFr } from "./sections/learning.fr";
import { pipelineDe } from "./sections/pipeline.de";
import { pipelineEn } from "./sections/pipeline.en";
import { pipelineFr } from "./sections/pipeline.fr";
import { shadowIdentityDe } from "./sections/shadowIdentity.de";
import { shadowIdentityEn } from "./sections/shadowIdentity.en";
import { shadowIdentityFr } from "./sections/shadowIdentity.fr";
import { shadowKnowledgeDe } from "./sections/shadowKnowledge.de";
import { shadowKnowledgeEn } from "./sections/shadowKnowledge.en";
import { shadowKnowledgeFr } from "./sections/shadowKnowledge.fr";
import { shadowMemoryDe } from "./sections/shadowMemory.de";
import { shadowMemoryEn } from "./sections/shadowMemory.en";
import { shadowMemoryFr } from "./sections/shadowMemory.fr";
import { shadowRelationshipDe } from "./sections/shadowRelationship.de";
import { shadowRelationshipEn } from "./sections/shadowRelationship.en";
import { shadowRelationshipFr } from "./sections/shadowRelationship.fr";
import { shadowTeachingDe } from "./sections/shadowTeaching.de";
import { shadowTeachingEn } from "./sections/shadowTeaching.en";
import { shadowTeachingFr } from "./sections/shadowTeaching.fr";
import { shellDe } from "./sections/shell.de";
import { shellEn } from "./sections/shell.en";
import { shellFr } from "./sections/shell.fr";
import { workspaceDe } from "./sections/workspace.de";
import { workspaceEn } from "./sections/workspace.en";
import { workspaceFr } from "./sections/workspace.fr";

const baseEn = {
	common: {
		loading: "Loading…",
		save: "Save",
		cancel: "Cancel",
		delete: "Delete",
		edit: "Edit",
		close: "Close",
		back: "Back",
		next: "Next",
		retry: "Retry",
		refresh: "Refresh",
		download: "Download",
		upload: "Upload",
		import: "Import",
		export: "Export",
		search: "Search",
		filter: "Filter",
		yes: "Yes",
		no: "No",
		or: "or",
		error: "Error",
		success: "Success",
		warning: "Warning",
		info: "Info",
		noResults: "No results",
		comingSoon: "Coming soon",
	},
	language: {
		label: "Language",
		en: "English",
		fr: "Français",
		de: "Deutsch",
	},
} as const;

export const en = mergeMessages(
	mergeMessages(
		mergeMessages(
			mergeMessages(
				mergeMessages(mergeMessages(baseEn, shellEn), pipelineEn),
				workspaceEn,
			),
			learningEn,
		),
		shadowIdentityEn,
	),
	mergeMessages(
		mergeMessages(shadowRelationshipEn, shadowMemoryEn),
		mergeMessages(shadowTeachingEn, shadowKnowledgeEn),
	),
);

export type Messages = DeepStringRecord<typeof en>;

const baseFr = {
	common: {
		loading: "Chargement…",
		save: "Enregistrer",
		cancel: "Annuler",
		delete: "Supprimer",
		edit: "Modifier",
		close: "Fermer",
		back: "Retour",
		next: "Suivant",
		retry: "Réessayer",
		refresh: "Actualiser",
		download: "Télécharger",
		upload: "Téléverser",
		import: "Importer",
		export: "Exporter",
		search: "Rechercher",
		filter: "Filtrer",
		yes: "Oui",
		no: "Non",
		or: "ou",
		error: "Erreur",
		success: "Succès",
		warning: "Avertissement",
		info: "Info",
		noResults: "Aucun résultat",
		comingSoon: "Bientôt disponible",
	},
	language: {
		label: "Langue",
		en: "English",
		fr: "Français",
		de: "Deutsch",
	},
} as const satisfies DeepStringRecord<typeof baseEn>;

export const fr = mergeMessages(
	mergeMessages(
		mergeMessages(
			mergeMessages(
				mergeMessages(mergeMessages(baseFr, shellFr), pipelineFr),
				workspaceFr,
			),
			learningFr,
		),
		shadowIdentityFr,
	),
	mergeMessages(
		mergeMessages(shadowRelationshipFr, shadowMemoryFr),
		mergeMessages(shadowTeachingFr, shadowKnowledgeFr),
	),
) satisfies Messages;

const baseDe = {
	common: {
		loading: "Wird geladen…",
		save: "Speichern",
		cancel: "Abbrechen",
		delete: "Löschen",
		edit: "Bearbeiten",
		close: "Schließen",
		back: "Zurück",
		next: "Weiter",
		retry: "Erneut versuchen",
		refresh: "Aktualisieren",
		download: "Herunterladen",
		upload: "Hochladen",
		import: "Importieren",
		export: "Exportieren",
		search: "Suchen",
		filter: "Filtern",
		yes: "Ja",
		no: "Nein",
		or: "oder",
		error: "Fehler",
		success: "Erfolg",
		warning: "Warnung",
		info: "Info",
		noResults: "Keine Ergebnisse",
		comingSoon: "Demnächst",
	},
	language: {
		label: "Sprache",
		en: "English",
		fr: "Français",
		de: "Deutsch",
	},
} as const satisfies DeepStringRecord<typeof baseEn>;

export const de = mergeMessages(
	mergeMessages(
		mergeMessages(
			mergeMessages(
				mergeMessages(mergeMessages(baseDe, shellDe), pipelineDe),
				workspaceDe,
			),
			learningDe,
		),
		shadowIdentityDe,
	),
	mergeMessages(
		mergeMessages(shadowRelationshipDe, shadowMemoryDe),
		mergeMessages(shadowTeachingDe, shadowKnowledgeDe),
	),
) satisfies Messages;
