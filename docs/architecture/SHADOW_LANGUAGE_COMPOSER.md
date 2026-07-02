# Shadow Language Composer

**Sprint:** 58  
**Product:** Lumen

---

## Purpose

Compose multilingual Shadow behavior: primary language, technical term policy, pronunciation, and summary language.

---

## Component

`backend/src/Application/ShadowIdentity/ShadowLanguageComposer.php`

### Policies

| Policy | Behavior |
| ------ | -------- |
| Always Original | Keep technical terms in source language |
| Always Translate | Translate all terms |
| Original + Explanation | Original term + short gloss |
| Adaptive | Context-dependent handling |

### Oral commands

Examples handled deterministically:

- "Explique en français."
- "Conserve les termes techniques anglais."
- "Le résumé est en allemand."
- "Prononce les acronymes en anglais."

---

## Output

Produces prompt instruction lines consumed by `ShadowAnswerEnricher` and Shadow watch prompts.

No LLM required for language rule selection.
