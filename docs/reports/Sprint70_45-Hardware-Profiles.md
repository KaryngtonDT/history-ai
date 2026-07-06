# Sprint 70.45 — Hardware Profiles & Blocked Engine Explainability

**Platform Sprint 70.45** adds hardware profile detection and blocked-engine explainability to Lumen Runtime.

## Delivered

| Slice | Deliverable |
|---|---|
| 01 | Domain: `HardwareProfile`, `HardwareCapability`, `HardwareRequirement`, etc. |
| 02 | Application detectors: GPU, CUDA, RAM, Docker, WSL, classifier |
| 03 | `EngineRequirementMatrix` per catalog engine |
| 04 | `EngineCompatibilityEvaluator`, blocked reason + alternative resolvers |
| 05 | API: `/hardware`, `/compatibility`, `/engines/{id}/blocked-reason` |
| 06 | Runtime UI: engine cards with HW badge + expandable explanation |
| 07 | Hardware profile section in `/settings/runtime` |
| 08 | Architecture + operations docs |
| 09 | Backend + frontend tests |
| 10 | Verification report |

## Reference machine

- Profile: `low_end_local`
- GPU: AMD Radeon integrated, no CUDA
- LatentSync / EchoMimic: blocked with Wav2Lip recommended
- FFmpeg CPU: ready without NVIDIA

## Out of scope

No engine installations in this sprint — detection and explainability only.
