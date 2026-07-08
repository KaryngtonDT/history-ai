# Sprint 72.1 Verification

## Summary

Introduced capability classification (Core, Optional, Premium, Experimental, Deprecated) and platform health model separating Core Health from extensions.

## Validation

| Check | Result |
|-------|--------|
| Backend PHPUnit | 1811 passed |
| Frontend Vitest | 701 passed |
| `make doctor` | Pass — Runtime Core ready |
| `make runtime-validate` | Exit 0 — Core 5/5 ready, status pass |
| `make runtime-benchmark` | Informational |
| `make runtime-completion` | Informational |

## Acceptance criteria

- [x] Runtime Core Health based on CORE capabilities only
- [x] Optional capabilities never make Runtime unhealthy
- [x] Premium capabilities remain visible when blocked
- [x] Doctor uses coreStatus instead of global engine fail
- [x] Dashboard separates Core / Optional / Premium
- [x] Split score model exposed via API
- [x] Shadow classification context
- [x] Validation exit code based on core health

## Key files

- `RuntimeCapabilityClassificationRegistry`
- `RuntimePlatformHealthService`
- `RuntimeScoreModel`
- `scripts/runtime_validate.py`
- Provision Center + Dashboard UI filters
