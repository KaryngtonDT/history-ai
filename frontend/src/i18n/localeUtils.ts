export type DeepStringRecord<T> = {
	[K in keyof T]: T[K] extends string ? string : DeepStringRecord<T[K]>;
};

export function mergeMessages<T extends object, U extends object>(
	base: T,
	extension: U,
): T & U {
	return { ...base, ...extension };
}
