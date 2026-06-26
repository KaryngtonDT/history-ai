Scenario 1

Given

I have a valid PDF

When

I upload it

Then

HTTP 201

Content created

ProcessingJob created

---

Scenario 2

Given

A file larger than limit

Then

HTTP 413

---

Scenario 3

Given

A DOCX

Then

HTTP 415

---

Scenario 4

Given

Storage unavailable

Then

HTTP 503
