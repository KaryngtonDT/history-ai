# Learning Agenda

Sprint 66 UI/backend concept for **today** and **upcoming** learning stacks.

## Structure

```text
ExecutiveAgenda
├── today: ExecutiveTask[]     (ordered: review → mission → watch → exercise)
├── upcoming: ExecutiveTask[]  (dated horizons)
└── metadata: scopeKey, generatedAt
```

## Task types

Align with `ExecutiveTask` domain: `review`, `mission`, `watch`, `exercise`, `checkpoint`, `pause`.

## API

- `GET /api/shadow/executive/agenda`
- Embedded in `GET /api/shadow/executive` dashboard payload

## Frontend

`LearningAgenda/` under `features/shadowExecutive/`, route `/settings/shadow/executive`.

## Energy-aware mode

When user declares available minutes (stretch), `EnergyAwarePlanner` trims or expands today's stack without adding new decision types.
