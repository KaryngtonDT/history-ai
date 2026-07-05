# Sprint 70.4 — Engine Provisioning Prompt

Use this prompt after the readiness audit (`Sprint70_4-Engine-Readiness-Audit.md`).

```text
Suite à l'audit Sprint 70.4, je veux maintenant passer en mode PROVISIONING.

Objectif

Je veux que Lumen soit complètement autonome concernant les moteurs IA officiellement supportés.

Si un moteur, un modèle, une dépendance ou une configuration manque, je veux que tu l'installes et le configures automatiquement lorsque cela est techniquement possible.

Le but est qu'à la fin de cette étape tous les moteurs officiellement supportés soient réellement utilisables.

Ne te contente pas de vérifier.

Installe.

Configure.

Teste.

Documente.

Puis valide.

Critère de succès

À la fin, chaque moteur est soit READY soit BLOCKED avec raison documentée.

Aucun état intermédiaire en résultat final.

Commandes

make provision-engines
make runtime-validate
make runtime-benchmark
```

See also: `scripts/provision-engines.sh`, `POST /api/runtime/provision`, `/settings/runtime` → Install All (Auto).
