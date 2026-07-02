# Knowledge Connections

Deterministic links between learned concepts in Shadow Memory.

## Builder

`KnowledgeConnectionBuilder` derives connections from the current knowledge graph:

- Docker → Kubernetes (containers to orchestration)
- Dependency Injection → Symfony Messenger (framework progression)
- GPU → CUDA (hardware to compute)

## Usage

- **Recall**: up to two connection labels appended to prompt context
- **UI**: `/settings/shadow/memory` connections section
- **Journey**: long-term steps may reference connection targets

## vs Relationship shared references

| Feature | Sprint 61 SharedReference | Sprint 62 KnowledgeConnection |
|---------|---------------------------|-------------------------------|
| Scope | Session / relationship context | Durable learning graph |
| Approval | May require user approval | Derived from observed learning |
| Purpose | Conversational rapport | Pedagogical sequencing |
