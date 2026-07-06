# Runtime Score

The **Overall Runtime Score** is a weighted composite (0–100) computed by `RuntimeScoreCalculator`.

| Component | Weight |
| --- | ---: |
| Runtime Health | 20% |
| Compatible Engines Installed | 20% |
| Engine Tests | 15% |
| Benchmarks | 15% |
| Documentation | 5% |
| Hardware Compatibility | 10% |
| Provisioning | 15% |

Each breakdown item includes:

- **score** — 0–100 for that dimension
- **explanation** — human-readable summary
- **improvement** — optional action when score &lt; 95%

## Grades

| Score | Grade |
| --- | --- |
| ≥ 90 | Excellent |
| ≥ 75 | Good |
| ≥ 60 | Fair |
| &lt; 60 | Needs attention |

## Platform Score

`PlatformScoreCalculator` averages component scores:

- Runtime (from overall runtime score)
- Shadow (persistence checks)
- Storage, Worker, Docker, PostgreSQL (production readiness)
- API (always 100 when endpoint is reachable)
- Documentation (shared with runtime documentation score)

Components with `null` score are marked `not_applicable`.

## Inputs (live)

| Input | Source |
| --- | --- |
| Runtime Health | ready engines / total engines |
| Compatible Installed | hardware-compatible engines at READY |
| Engine Tests | runtime health score |
| Benchmarks | recent benchmark pass rate |
| Documentation | presence of key runtime docs in repo |
| Hardware Compatibility | compatible engines / catalog |
| Provisioning | provisioned compatible / plan total |

No hard-coded or mock values are injected in the assembler.
