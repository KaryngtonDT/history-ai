# YouTube Original Transcript Choice Flow

## Import behaviour

1. Download video via `yt-dlp`
2. Fetch **original-language captions only** (manual preferred, then auto)
3. Never import translated YouTube captions in Sprint 70.8

## If captions exist

- Pipeline job → `waiting_user_choice`
- User options: `youtube_transcript` | `local_engine`
- No automatic pipeline continuation

## If captions missing

- Estimate local STT duration
- Start background transcription job
- Notify user; stop automatic continuation

## Caption fetcher

`YtDlpYouTubeCaptionFetcher` uses `--sub-langs {original}` with `--write-subs` then `--write-auto-subs`.

Pending captions stored in `{youtube.download_dir}/{videoId}-captions.json` until choice applied.
