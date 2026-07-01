import { createContext, useContext, useMemo } from "react";
import { useParams } from "react-router";

interface ProductContextValue {
	videoId: string | null;
}

const ProductContext = createContext<ProductContextValue>({ videoId: null });

export function ProductContextProvider({
	children,
}: {
	children: React.ReactNode;
}) {
	const params = useParams();
	const videoId = typeof params.videoId === "string" ? params.videoId : null;
	const value = useMemo(() => ({ videoId }), [videoId]);

	return (
		<ProductContext.Provider value={value}>{children}</ProductContext.Provider>
	);
}

export function useProductContext(): ProductContextValue {
	return useContext(ProductContext);
}
