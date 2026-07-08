import { Link } from "react-router";
import { PageIntroduction } from "@/features/product";
import { RuntimeProvisionCenter } from "@/features/runtime/RuntimeProvisionCenter";

export function RuntimeEnginesPage() {
	return (
		<section>
			<PageIntroduction
				eyebrow="Settings"
				title="AI Engine Manager"
				description="Install, update, validate, benchmark, and select engines for every Runtime capability."
				whatCanIDo="Manage the full engine lifecycle and choose Auto, Manual, or Locked selection per capability."
				secondaryActions={
					<>
						<Link to="/settings/runtime">Runtime Center</Link>
						{" · "}
						<Link to="/settings/runtime/analytics">Analytics</Link>
					</>
				}
			/>
			<RuntimeProvisionCenter />
		</section>
	);
}
