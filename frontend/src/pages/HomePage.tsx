import { useAppStore } from "@/store/appStore";

export function HomePage() {
	const ready = useAppStore((state) => state.ready);

	return (
		<main className="flex min-h-screen flex-col items-center justify-center bg-slate-950 text-slate-50">
			<h1 className="text-4xl font-bold tracking-tight">History AI</h1>
			<p className="mt-4 text-xl text-slate-300">
				{ready ? "Frontend Ready" : "Loading..."}
			</p>
			<p className="mt-2 text-sm text-slate-500">React + TypeScript + Vite</p>
		</main>
	);
}
