# Pipeline Invalidation

Restarting a stage invalidates downstream stages per `PipelineDependencyResolver`:

| Restart | Invalidates |
|---------|-------------|
| speech_to_text | translation, audio, voice clone, lip sync, render, quality |
| translation | audio, voice clone, lip sync, render, quality |
| text_to_speech | voice clone, lip sync, render, quality |
| voice_clone | lip sync, render, quality |
| lip_sync | render, quality |
| video_render | quality |

Previous artifacts are **not deleted**. Dependent jobs are cancelled and artifact IDs recorded in `staleArtifactIds`.

Restart requires confirmation in UI: *"Restarting this stage will invalidate later stages."*
