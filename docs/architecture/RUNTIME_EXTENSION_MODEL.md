# Runtime Extension Model

Optional and premium capabilities are **extensions** to the Runtime Core.

## Optional extensions

OCR, Vision, Embeddings, and Reranking are:

- Not required for core video pipeline health
- Not installed by default
- Installable from `/settings/runtime/engines`
- Visible in Extension Coverage with status `NOT INSTALLED`, `BLOCKED`, or `READY`

## Premium extensions

Lip Sync premium engines (e.g. LatentSync) are:

- Visible when blocked by hardware
- Reported under Premium Availability
- Explained with `futureHardware` hints (recommended GPU, estimated premium score gain)
- Never counted as Core Runtime failures

## User experience

Users may hide optional capabilities in the Provision Center. Counters show:

- Core: N / N Ready
- Optional: N / N Installed
- Premium: N / N Available

Shadow uses classification to explain why Runtime remains healthy when premium engines are blocked.
