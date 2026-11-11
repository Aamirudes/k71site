#!/usr/bin/env node
/**
 * K71 Video-to-Scroll-Frames CLI Converter
 *
 * Converts an MP4/WebM/MOV video into naturally-ordered WebP frames ready for the
 * homepage pinned canvas scrollytelling stage (`public/images/scroll-frames/`).
 *
 * Usage:
 *   node tools/convert-video-frames.mjs path/to/video.mp4
 *   node tools/convert-video-frames.mjs path/to/video.mp4 --frames=90 --width=1280 --quality=0.84 --out=public/images/scroll-frames
 */

import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import { execFileSync, spawnSync } from 'node:child_process';

const args = process.argv.slice(2);
const positional = args.filter((a) => !a.startsWith('--'));
const flags = Object.fromEntries(
    args
        .filter((a) => a.startsWith('--'))
        .map((a) => {
            const [k, v = 'true'] = a.slice(2).split('=');
            return [k, v];
        })
);

const videoInput = positional[0];
if (!videoInput || flags.help !== undefined) {
    console.log(`
K71 Video-to-Scroll-Frames Converter

Usage:
  node tools/convert-video-frames.mjs <video-file> [options]

Options:
  --frames=90                        Target number of output frames (default: 90)
  --width=1280                       Max frame width in pixels (default: 1280)
  --quality=0.84                     WebP compression quality 0.1..1.0 (default: 0.84)
  --out=public/images/scroll-frames  Output directory (default: public/images/scroll-frames)
  --keep                             Do not delete existing images inside --out
`);
    process.exit(videoInput ? 0 : 1);
}

const videoPath = path.resolve(process.cwd(), videoInput);
if (!fs.existsSync(videoPath)) {
    console.error(`Error: Video file not found: ${videoPath}`);
    process.exit(1);
}

const targetFrames = Math.min(360, Math.max(8, Number(flags.frames || 90)));
const maxWidth = Math.min(2560, Math.max(320, Number(flags.width || 1280)));
const quality = Math.min(0.98, Math.max(0.4, Number(flags.quality || 0.84)));
const outDir = path.resolve(process.cwd(), flags.out || 'public/images/scroll-frames');
const clearOut = flags.keep === undefined;

fs.mkdirSync(outDir, { recursive: true });
if (clearOut) {
    for (const file of fs.readdirSync(outDir)) {
        if (/\.(webp|jpe?g|png)$/i.test(file)) {
            fs.unlinkSync(path.join(outDir, file));
        }
    }
}

function hasFfmpeg() {
    try {
        const res = spawnSync('ffmpeg', ['-version'], { stdio: 'ignore' });
        return res.status === 0;
    } catch {
        return false;
    }
}

function runFfmpeg() {
    let duration = 0;
    try {
        const probeOut = execFileSync(
            'ffprobe',
            ['-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', videoPath],
            { encoding: 'utf8' }
        ).trim();
        duration = parseFloat(probeOut);
    } catch {
        duration = 0;
    }

    const vfParts = [`scale='min(${maxWidth},iw)':-2`];
    if (Number.isFinite(duration) && duration > 0.1) {
        const fps = (targetFrames / duration).toFixed(4);
        vfParts.unshift(`fps=${fps}`);
    }

    const qWebp = Math.round(quality * 100);
    const outPattern = path.join(outDir, 'frame_%04d.webp');
    console.log(`[ffmpeg] Extracting up to ${targetFrames} WebP frames (${maxWidth}px, q=${qWebp}) -> ${outDir}`);

    execFileSync(
        'ffmpeg',
        [
            '-y',
            '-i', videoPath,
            '-vf', vfParts.join(','),
            '-frames:v', String(targetFrames),
            '-c:v', 'libwebp',
            '-lossless', '0',
            '-q:v', String(qWebp),
            '-an',
            outPattern,
        ],
        { stdio: 'inherit' }
    );

    const written = fs.readdirSync(outDir).filter((f) => /\.(webp|jpe?g|png)$/i.test(f));
    console.log(`\nDone! Generated ${written.length} frames in ${outDir}`);
}

async function launchHeadlessBrowser(chromium) {
    for (const opts of [
        { headless: true },
        { headless: true, channel: 'chrome' },
        { headless: true, channel: 'msedge' },
    ]) {
        try {
            return await chromium.launch(opts);
        } catch {}
    }
    throw new Error('Could not launch Chromium, Chrome, or Edge. Install ffmpeg or run: npx playwright install chromium');
}

async function runPlaywrightFallback() {
    const { chromium } = await import('playwright');
    const stat = fs.statSync(videoPath);
    const ext = path.extname(videoPath).toLowerCase();
    const mime = ext === '.webm' ? 'video/webm' : ext === '.mov' ? 'video/quicktime' : 'video/mp4';

    const server = http.createServer((req, res) => {
        if (req.url === '/video') {
            const range = req.headers.range;
            if (range) {
                const parts = range.replace(/bytes=/, '').split('-');
                const start = parseInt(parts[0], 10);
                const end = parts[1] ? parseInt(parts[1], 10) : stat.size - 1;
                res.writeHead(206, {
                    'Content-Range': `bytes ${start}-${end}/${stat.size}`,
                    'Accept-Ranges': 'bytes',
                    'Content-Length': end - start + 1,
                    'Content-Type': mime,
                });
                fs.createReadStream(videoPath, { start, end }).pipe(res);
            } else {
                res.writeHead(200, {
                    'Content-Length': stat.size,
                    'Content-Type': mime,
                    'Accept-Ranges': 'bytes',
                });
                fs.createReadStream(videoPath).pipe(res);
            }
            return;
        }

        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end('<!doctype html><html><body><video id="v" muted playsinline preload="auto" src="/video"></video><canvas id="c"></canvas></body></html>');
    });

    await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
    const port = server.address().port;
    const browser = await launchHeadlessBrowser(chromium);

    try {
        console.log(`[playwright] ffmpeg not found; using headless Chromium fallback -> ${outDir}`);
        const page = await browser.newPage();
        await page.goto(`http://127.0.0.1:${port}/`, { waitUntil: 'load' });

        await page.waitForFunction(() => {
            const v = document.getElementById('v');
            return v && Number.isFinite(v.duration) && v.duration > 0 && v.videoWidth > 0;
        }, null, { timeout: 30000 });

        const meta = await page.evaluate(() => {
            const v = document.getElementById('v');
            return { duration: v.duration, width: v.videoWidth, height: v.videoHeight };
        });

        const outW = Math.min(maxWidth, meta.width);
        const outH = Math.max(2, Math.round(outW * (meta.height / meta.width)));
        console.log(`Video ${meta.width}x${meta.height}, ${meta.duration.toFixed(2)}s -> ${targetFrames} frames (${outW}x${outH})`);

        for (let i = 0; i < targetFrames; i++) {
            const t = Math.min(meta.duration - 0.02, Math.max(0, (i / Math.max(1, targetFrames - 1)) * (meta.duration - 0.03)));
            const dataUrl = await page.evaluate(async ({ seekT, w, h, q }) => {
                const v = document.getElementById('v');
                const c = document.getElementById('c');
                c.width = w;
                c.height = h;
                const ctx = c.getContext('2d');
                await new Promise((res) => {
                    const done = () => res();
                    v.addEventListener('seeked', done, { once: true });
                    setTimeout(done, 2000);
                    v.currentTime = seekT;
                });
                ctx.drawImage(v, 0, 0, w, h);
                return c.toDataURL('image/webp', q);
            }, { seekT: t, w: outW, h: outH, q: quality });

            const b64 = dataUrl.split(',')[1];
            const outFile = path.join(outDir, `frame_${String(i + 1).padStart(4, '0')}.webp`);
            fs.writeFileSync(outFile, Buffer.from(b64, 'base64'));
            process.stdout.write(`\rExtracted frame ${i + 1} / ${targetFrames}`);
        }
        console.log(`\nDone! Generated ${targetFrames} frames in ${outDir}`);
    } finally {
        await browser.close();
        server.close();
    }
}

if (hasFfmpeg()) {
    runFfmpeg();
} else {
    runPlaywrightFallback().catch((err) => {
        console.error('\nFailed:', err.message);
        process.exit(1);
    });
}
