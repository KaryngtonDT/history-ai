export function deriveTitleFromPdfFileName(fileName: string): string {
	const withoutExtension = fileName.replace(/\.pdf$/i, "").trim();

	return withoutExtension.length > 0 ? withoutExtension : fileName;
}
