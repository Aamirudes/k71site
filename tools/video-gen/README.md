# K71 Video Generator — Security Notice

This folder contains a **LOCAL-ONLY** command-line tool.

## ⚠️ NEVER:
- Run this as a web endpoint or expose it via any URL
- Commit your real API key to Git
- Put your GEMINI_API_KEY in any frontend JS or PHP file

## ✅ Safe because:
- Reads the key from `../../.env` (which is in .gitignore)
- Only runs when YOU manually execute it on your computer
- Output goes to `public/videos/intro/` as finished .mp4 files

## Setup & Usage — see the full guide in `SETUP-GUIDE.md`

