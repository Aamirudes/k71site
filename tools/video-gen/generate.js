/**
 * K71 Intro Video Generator
 * ─────────────────────────────────────────────────────────────────
 * LOCAL UTILITY ONLY — never run this as a web endpoint.
 * Uses Gemini Omni Flash (gemini-omni-1.1-flash) via the official
 * Google Gen AI SDK (@google/genai) — Interactions API.
 *
 * Usage:
 *   node generate.js --test           → Test API key, no video generated
 *   node generate.js                  → Generate all 5 intro clips
 *   node generate.js --clip 1         → Generate only clip 1
 *   node generate.js --clip 1,3,5     → Generate clips 1, 3, and 5
 *
 * Output: ../../public/videos/intro/clip-01.mp4  ...  clip-05.mp4
 * ─────────────────────────────────────────────────────────────────
 */

import { createRequire } from 'module';
import { readFileSync, existsSync, writeFileSync, mkdirSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

// ── Load .env from project root (two levels up from tools/video-gen/) ──────
const envPath = resolve(__dirname, '../../.env');
if (!existsSync(envPath)) {
  console.error('\n❌  ERROR: .env file not found at:', envPath);
  console.error('   Make sure you are running this from inside tools/video-gen/\n');
  process.exit(1);
}

const envContent = readFileSync(envPath, 'utf-8');
for (const line of envContent.split('\n')) {
  const trimmed = line.trim();
  if (!trimmed || trimmed.startsWith('#')) continue;
  const eqIdx = trimmed.indexOf('=');
  if (eqIdx === -1) continue;
  const key = trimmed.slice(0, eqIdx).trim();
  const val = trimmed.slice(eqIdx + 1).trim();
  if (!process.env[key]) process.env[key] = val;
}

// ── Validate API key presence ───────────────────────────────────────────────
const API_KEY = process.env.GEMINI_API_KEY;

if (!API_KEY || API_KEY === 'your_real_key_goes_here' || API_KEY === 'your_gemini_api_key_here') {
  console.error('\n❌  ERROR: GEMINI_API_KEY is missing or is still a placeholder.');
  console.error('   Open your project .env file and replace the placeholder with your real key.');
  console.error('   Get a key at: https://aistudio.google.com/apikey\n');
  process.exit(1);
}

// ── Import SDK ──────────────────────────────────────────────────────────────
let GoogleGenAI;
try {
  ({ GoogleGenAI } = await import('@google/genai'));
} catch (e) {
  console.error('\n❌  ERROR: @google/genai package not found.');
  console.error('   Run this first:  npm install');
  console.error('   Then try again.\n');
  process.exit(1);
}

const ai = new GoogleGenAI({ apiKey: API_KEY });
const MODEL = 'gemini-omni-1.1-flash';

// ── Output folder ───────────────────────────────────────────────────────────
const OUTPUT_DIR = resolve(__dirname, '../../public/videos/intro');
mkdirSync(OUTPUT_DIR, { recursive: true });

// ── The 5 cinematic intro clips ─────────────────────────────────────────────
const CLIPS = [
  {
    id: 1,
    filename: 'clip-01.mp4',
    label: 'Deep Space & Milky Way',
    prompt: `Cinematic opening shot: vast deep space, billions of stars glowing, the Milky Way galaxy 
stretching across the frame in breathtaking detail. Slow cinematic camera drift through the cosmic 
void. Ultra-high definition, photorealistic, 8K quality. Deep blues, purples, and whites. 
Film grain, anamorphic lens flares. Duration: 8 seconds.`,
  },
  {
    id: 2,
    filename: 'clip-02.mp4',
    label: 'Journey Through the Solar System',
    prompt: `Cinematic journey through our solar system: starting at the outer planets, sweeping past 
Saturn's iconic rings, past Jupiter's swirling storms, past Mars and the asteroid belt, accelerating 
toward the inner solar system. Photorealistic planets, accurate scale and lighting. IMAX cinematic 
quality, dramatic orchestral feel. Duration: 8 seconds.`,
  },
  {
    id: 3,
    filename: 'clip-03.mp4',
    label: 'Approaching the Sun',
    prompt: `Cinematic approach toward the Sun: the camera accelerates through space, the Sun growing 
larger and more magnificent, solar flares erupting dramatically at the edges, intense golden-orange 
light flooding the frame. Heat shimmer, chromatic aberration effects. Photorealistic, cinematic 
quality. Duration: 6 seconds.`,
  },
  {
    id: 4,
    filename: 'clip-04.mp4',
    label: 'Solar Granulation Close-Up',
    prompt: `Extreme close-up of the Sun's surface: golden-yellow solar granulation in stunning detail, 
convection cells boiling and churning, plasma flows moving across the surface, magnetic field lines 
visible as bright filaments. Rich golden, amber, and yellow tones. Photorealistic NASA-quality 
imagery. Slow cinematic motion. Duration: 6 seconds.`,
  },
  {
    id: 5,
    filename: 'clip-05.mp4',
    label: 'Transition to K71 Marketplace',
    prompt: `The golden solar surface slowly dissolves and transitions into a modern, premium Indian 
marketplace — warm golden light, vibrant product displays, satisfied shoppers, elegant UI on mobile 
screens. The colour palette shifts from solar gold to clean white and deep black branding. 
Smooth, elegant dissolve transition. Cinematic quality. Duration: 6 seconds.`,
  },
];

// ── Parse CLI arguments ─────────────────────────────────────────────────────
const args = process.argv.slice(2);
const isTestOnly = args.includes('--test');
const clipFlagIdx = args.indexOf('--clip');
let clipsToRun = CLIPS;

if (clipFlagIdx !== -1 && args[clipFlagIdx + 1]) {
  const ids = args[clipFlagIdx + 1].split(',').map(Number).filter(Boolean);
  clipsToRun = CLIPS.filter(c => ids.includes(c.id));
  if (clipsToRun.length === 0) {
    console.error(`\n❌  No valid clip IDs found. Valid IDs are 1–5. You gave: ${args[clipFlagIdx + 1]}\n`);
    process.exit(1);
  }
}

// ── Pretty logger ────────────────────────────────────────────────────────────
const log = {
  info:    (msg) => console.log(`\n  ℹ️   ${msg}`),
  ok:      (msg) => console.log(`  ✅  ${msg}`),
  warn:    (msg) => console.log(`  ⚠️   ${msg}`),
  error:   (msg) => console.error(`  ❌  ${msg}`),
  step:    (msg) => console.log(`\n  🎬  ${msg}`),
  divider: ()    => console.log('\n' + '─'.repeat(60)),
};

// ── Test configuration (--test flag) ────────────────────────────────────────
async function testConfig() {
  log.divider();
  console.log('\n  🔧  K71 VIDEO GEN — CONFIG TEST\n');
  log.info(`API Key found: ${API_KEY.slice(0, 8)}${'*'.repeat(20)} (last 4: ${API_KEY.slice(-4)})`);
  log.info(`Model: ${MODEL}`);
  log.info(`Output folder: ${OUTPUT_DIR}`);
  log.info(`Clips configured: ${CLIPS.length}`);

  console.log('\n  📋  Clips to be generated:');
  for (const clip of CLIPS) {
    const exists = existsSync(resolve(OUTPUT_DIR, clip.filename));
    console.log(`       Clip ${clip.id}: ${clip.label} → ${clip.filename} ${exists ? '(already exists — will SKIP)' : '(will generate)'}`);
  }

  // Make a lightweight API call to verify key works (text generation, no credits wasted on video)
  log.step('Testing API key with a lightweight text request...');
  try {
    const result = await ai.models.generateContent({
      model: 'gemini-2.0-flash',
      contents: 'Reply with exactly: KEY_OK',
    });
    const text = result.candidates?.[0]?.content?.parts?.[0]?.text ?? '';
    if (text.includes('KEY_OK')) {
      log.ok('API key is VALID and working! ✓');
    } else {
      log.ok(`API responded (key works). Response: "${text.trim()}"`);
    }
  } catch (err) {
    log.error('API key test FAILED.');
    if (err.message?.includes('API_KEY_INVALID') || err.status === 400) {
      log.error('Your API key is invalid. Please check it at https://aistudio.google.com/apikey');
    } else if (err.status === 429) {
      log.warn('Rate limit hit during test. Key probably works, but you are being throttled.');
    } else {
      log.error(`Error details: ${err.message}`);
    }
    process.exit(1);
  }

  log.divider();
  console.log('\n  ✅  Configuration looks good! When you are ready to generate videos, run:');
  console.log('      node generate.js\n');
}

// ── Generate a single clip ───────────────────────────────────────────────────
async function generateClip(clip) {
  const outputPath = resolve(OUTPUT_DIR, clip.filename);

  if (existsSync(outputPath)) {
    log.warn(`Clip ${clip.id} (${clip.filename}) already exists — SKIPPING.`);
    log.warn(`Delete the file first if you want to regenerate it.`);
    return { skipped: true };
  }

  log.step(`Generating Clip ${clip.id}: "${clip.label}"`);
  console.log(`      Prompt: ${clip.prompt.slice(0, 80).trim()}...`);
  console.log(`      Model:  ${MODEL}`);
  console.log(`      Output: ${outputPath}`);
  console.log(`      ⏳ This may take 30–120 seconds...`);

  const startTime = Date.now();

  try {
    const interaction = await ai.interactions.create({
      model: MODEL,
      input: clip.prompt,
      response_format: {
        type: 'video',
        aspect_ratio: '16:9',
      },
    });

    if (!interaction?.output_video?.data) {
      throw new Error('API returned no video data. The response was: ' + JSON.stringify(interaction));
    }

    const videoBuffer = Buffer.from(interaction.output_video.data, 'base64');
    writeFileSync(outputPath, videoBuffer);

    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
    const sizeMB = (videoBuffer.length / 1024 / 1024).toFixed(2);
    log.ok(`Clip ${clip.id} saved! (${sizeMB} MB, took ${elapsed}s)`);
    return { success: true, path: outputPath, sizeMB };

  } catch (err) {
    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
    log.error(`Clip ${clip.id} FAILED after ${elapsed}s.`);

    if (err.status === 429) {
      log.warn('QUOTA LIMIT: You have hit your API rate limit or daily quota.');
      log.warn('Wait a few minutes and try again, or check your quota at:');
      log.warn('https://console.cloud.google.com/apis/api/generativelanguage.googleapis.com/quotas');
    } else if (err.status === 403) {
      log.error('PERMISSION DENIED: Your API key may not have Gemini Omni access.');
      log.error('Check your key at https://aistudio.google.com/apikey');
    } else if (err.status === 400) {
      log.error('BAD REQUEST: Check your prompt or model ID.');
      log.error(`Details: ${err.message}`);
    } else {
      log.error(`Error: ${err.message}`);
    }
    return { error: true, clip: clip.id };
  }
}

// ── Main entry point ─────────────────────────────────────────────────────────
async function main() {
  if (isTestOnly) {
    await testConfig();
    return;
  }

  log.divider();
  console.log('\n  🚀  K71 VIDEO GEN — STARTING\n');
  console.log(`  Generating ${clipsToRun.length} clip(s) using ${MODEL}`);
  console.log(`  Output folder: ${OUTPUT_DIR}`);
  console.log('\n  ⚠️   NOTE: Each clip uses your Gemini API quota.');
  console.log('  ⚠️   Do NOT close this window while generating.\n');

  const results = [];
  for (const clip of clipsToRun) {
    const result = await generateClip(clip);
    results.push({ clip, result });
    // Small pause between requests to avoid rate limiting
    if (clipsToRun.indexOf(clip) < clipsToRun.length - 1) {
      console.log('      ⏸️  Pausing 3s before next clip...');
      await new Promise(r => setTimeout(r, 3000));
    }
  }

  // Summary
  log.divider();
  console.log('\n  📊  GENERATION SUMMARY\n');
  for (const { clip, result } of results) {
    if (result.skipped)      console.log(`       Clip ${clip.id}: ⏭️  SKIPPED (already exists)`);
    else if (result.success) console.log(`       Clip ${clip.id}: ✅  SUCCESS → ${clip.filename} (${result.sizeMB} MB)`);
    else                     console.log(`       Clip ${clip.id}: ❌  FAILED`);
  }

  const failed = results.filter(r => r.result.error);
  if (failed.length > 0) {
    console.log(`\n  ⚠️   ${failed.length} clip(s) failed. Re-run with:`);
    console.log(`       node generate.js --clip ${failed.map(r => r.clip.id).join(',')}`);
  } else {
    console.log('\n  🎉  All done! Your videos are ready in: public/videos/intro/');
  }
  log.divider();
  console.log('');
}

main().catch(err => {
  console.error('\n  💥  Unexpected error:', err.message);
  process.exit(1);
});

