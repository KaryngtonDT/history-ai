Scenario 1 — Button variants

Given

Storybook is running

When

I open the Button stories

Then

I see Primary, Secondary, Danger, and Disabled variants

---

Scenario 2 — Dropzone states

Given

Storybook is running

When

I open the Dropzone stories

Then

I see Empty, Hover, Uploading, Error, and Completed states

---

Scenario 3 — Dashboard shell

Given

I open the app at /

When

The page loads

Then

I see "History AI" header

And import action buttons (PDF, YouTube, Audio)

And empty state message "No content yet"

And no network requests to the backend

---

Scenario 4 — Component tests

Given

Generic components exist

When

Vitest component tests run

Then

All component tests pass

---

Scenario 5 — Lint and build

Given

UI Foundation is complete

When

I run npm run check and npm run build

Then

Both succeed with no errors
