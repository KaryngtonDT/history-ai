export type Messages = {
	common: {
		loading: string;
		save: string;
		cancel: string;
		delete: string;
		edit: string;
		close: string;
		back: string;
		next: string;
		retry: string;
		refresh: string;
		download: string;
		upload: string;
		import: string;
		export: string;
		search: string;
		filter: string;
		yes: string;
		no: string;
		or: string;
		error: string;
		success: string;
		warning: string;
		info: string;
		noResults: string;
		comingSoon: string;
	};
	language: {
		label: string;
		en: string;
		fr: string;
		de: string;
	};
};

export const en = {
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
} as const satisfies Messages;

export type EnMessages = typeof en;
