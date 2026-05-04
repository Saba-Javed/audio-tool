# 🎵 Audio Tool — Trim & Compress

A complete browser-based audio editing tool built with **PHP + FFmpeg + WaveSurfer.js**.  
Supports trimming, bitrate compression, and both operations combined — all with a modern UI and zero page reloads.

---

## 📁 File Structure

```
audio-tool/
├── index.php       ← Homepage / upload landing page
├── editor.php      ← Combined trim + compress editor
├── upload.php      ← Upload handler (JSON API)
├── process.php     ← FFmpeg processor (JSON API)
├── .htaccess       ← Security + upload limits + forced downloads
├── uploads/        ← Auto-created: uploaded source files
└── output/         ← Auto-created: processed output files
```

---

## ⚙️ Setup on XAMPP

### 1. Copy files
Place all files in your XAMPP htdocs folder:
```
C:/xampp/htdocs/audio-tool/
```

### 2. Verify FFmpeg
Open a terminal (CMD or PowerShell) and run:
```bash
ffmpeg -version
```
If it prints version info, you're good.

If you get "not found", either:
- Add FFmpeg to your Windows PATH, **or**
- Open `process.php` line 14 and set the full path:
```php
$ffmpeg = 'C:/ffmpeg/bin/ffmpeg.exe';
```

### 3. Increase PHP upload limit (for files > 8 MB)
Edit `C:/xampp/php/php.ini`:
```ini
upload_max_filesize = 100M
post_max_size       = 105M
max_execution_time  = 180
memory_limit        = 256M
```
Then **restart Apache** in XAMPP Control Panel.

### 4. Open the tool
```
http://localhost/audio-tool/
```

---

## 🚀 How It Works

| Step | What happens |
|------|-------------|
| 1 | User clicks **Choose File** on the homepage |
| 2 | File uploads via AJAX with a live progress bar |
| 3 | `upload.php` validates + saves to `/uploads/` |
| 4 | Browser redirects to `editor.php` automatically |
| 5 | WaveSurfer.js renders the audio waveform |
| 6 | User picks a mode: **Trim**, **Compress**, or **Trim + Compress** |
| 7 | User adjusts sliders / quality cards |
| 8 | Clicks the action button → AJAX POST to `process.php` |
| 9 | FFmpeg runs, output saved to `/output/` |
| 10 | Download button appears + auto-download triggers |

---

## 🎛️ Operation Modes

### ✂️ Trim Only
Uses FFmpeg **stream copy** (`-c copy`) — no re-encoding.  
Result: near-instant, zero quality loss, exact cut.

FFmpeg command used:
```
ffmpeg -ss [start] -to [end] -i input.mp3 -c copy -avoid_negative_ts make_zero output.mp3
```

### 📦 Compress Only
Re-encodes to selected bitrate. For WAV/FLAC source files, output is converted to MP3 automatically.

FFmpeg command used:
```
ffmpeg -i input.wav -codec:a libmp3lame -b:a 128k -vn output.mp3
```

### ⚡ Trim + Compress
Trims and re-encodes in a single FFmpeg pass.

FFmpeg command used:
```
ffmpeg -ss [start] -to [end] -i input.mp3 -codec:a libmp3lame -b:a 128k -avoid_negative_ts make_zero output.mp3
```

---

## 🎚️ Compression Quality Presets

| Preset | Bitrate | Best For |
|--------|---------|----------|
| High   | 192 kbps | Music, podcasts where quality matters |
| Medium | 128 kbps | General use, balanced size/quality |
| Low    | 64 kbps  | Voice recordings, maximum compression |

The editor shows a **live estimated output size** as you change presets.

---

## 🎧 Supported Formats

| Format | Upload | Output |
|--------|--------|--------|
| MP3    | ✅ | ✅ same format |
| WAV    | ✅ | ✅ (MP3 for compress/both) |
| OGG    | ✅ | ✅ same format |
| M4A    | ✅ | ✅ same format |
| FLAC   | ✅ | ✅ (MP3 for compress/both) |
| AAC    | ✅ | ✅ same format |
| AIFF   | ✅ | ✅ (MP3 for compress/both) |
| OPUS   | ✅ | ✅ same format |

---

## 🔧 Configuration Reference

| Setting | File | Variable | Default |
|---------|------|----------|---------|
| FFmpeg binary path | `process.php` | `$ffmpeg` | `ffmpeg` (uses PATH) |
| Max upload size | `upload.php` | `$maxBytes` | 100 MB |
| Max trim duration | `process.php` | `$maxDur` | 7200 s (2 hr) |
| Allowed bitrates | `process.php` | `$validBitrates` | 64k, 128k, 192k |

---

## 🧹 Optional: Auto-cleanup Old Files

Add a scheduled task (Windows) or cron job (Linux/Mac) to delete old processed files:

**Linux/Mac cron** (runs daily at 2am):
```bash
0 2 * * * find /path/to/audio-tool/uploads/ -mtime +1 -delete
0 2 * * * find /path/to/audio-tool/output/  -mtime +1 -delete
```

**Windows Task Scheduler** (PowerShell):
```powershell
Get-ChildItem "C:\xampp\htdocs\audio-tool\uploads" | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-1) } | Remove-Item
```

---

## ❓ Troubleshooting

| Problem | Solution |
|---------|----------|
| "FFmpeg not found" | Set full path in `process.php` → `$ffmpeg` |
| Upload fails for large files | Increase `upload_max_filesize` in `php.ini` and restart Apache |
| Waveform doesn't load | Open browser DevTools console — check if the audio URL is accessible |
| "Source file not found" | The file may have been deleted from `/uploads/` — re-upload |
| Output file is 0 bytes | FFmpeg ran but failed silently — check PHP error log at `C:/xampp/apache/logs/error.log` |
| WAV file sounds wrong after compress | Normal — WAV → MP3 re-encoding. Use High (192k) for best quality |
| Download doesn't auto-trigger | Browser blocked it — click the green Download button manually |
