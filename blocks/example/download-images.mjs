#!/usr/bin/env node
// =============================================================================
// download-images.js  —  Universal Figma Asset Downloader
// Works on Windows, macOS, and Linux. Node.js 18+. No dependencies.
//
// Paste ANY Figma design URL from ANY page/frame and it downloads every image.
//
// ── QUICK START ───────────────────────────────────────────────────────────────
//
//   node download-images.js <token> "<figma-url>"
//
//   Example (just paste the URL straight from your browser):
//     node download-images.js figd_xxx \
//       "https://www.figma.com/design/RVpIxWGvsIx9DHZgDdkLKT/Site?node-id=10503-322"
//
//   The file key and node ID are parsed automatically from the URL.
//
// ── MODES ────────────────────────────────────────────────────────────────────
//
// 1. URL MODE (easiest) — paste a Figma link, scans that frame/page:
//      node download-images.js <token> "<figma-url>" [options]
//
// 2. AUTO MODE — pass file key + node ID separately:
//      node download-images.js <token> --file <fileKey> --node <nodeId> [options]
//      node download-images.js <token> --file <fileKey>           # whole file
//
// 3. MAP MODE — explicit filename → nodeId mapping for full naming control:
//      node download-images.js <token> --file <fileKey> --map <map.json> [options]
//
//    map file format:
//    {
//      "hero-bg.jpg": { "nodeId": "10503:324", "format": "jpg" },
//      "logo.svg":    { "nodeId": "10316:496", "format": "svg" }
//    }
//
// ── OPTIONS ───────────────────────────────────────────────────────────────────
//   --url <url>       Figma design URL (file key + node parsed automatically)
//   --file <key>      Figma file key (if not using --url)
//   --node <id>       Node/frame/page ID to scan (omit = whole file)
//   --map <file>      JSON map of filename→{nodeId,format}
//   --out <dir>       Output directory                 (default: ./assets/images)
//   --format <fmt>    Default export format svg|png|jpg (default: png)
//   --scale <n>       Export scale 1-4                  (default: 2)
//   --concurrency <n> Parallel downloads               (default: 8)
//   --flat            Use clean names (no node-id suffix); may overwrite on clash
//   --dry-run         Resolve URLs and print them, don't download
//   --overwrite       Re-download files that already exist
//
// ── ENV ───────────────────────────────────────────────────────────────────────
//   FIGMA_TOKEN       Alternative to passing token as first arg
//
// =============================================================================

import https from "https";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const FIGMA_API = "https://api.figma.com/v1";

// ── Figma URL parser ──────────────────────────────────────────────────────────
// Handles:
//   https://www.figma.com/design/<fileKey>/<name>?node-id=10503-322
//   https://figma.com/file/<fileKey>/<name>?node-id=10503:322
//   https://www.figma.com/design/<fileKey>/branch/<branchKey>/<name>?node-id=...
function parseFigmaUrl(url) {
  const out = { fileKey: null, nodeId: null };
  try {
    const u = new URL(url);
    // file key: /design/<key>/...  or  /file/<key>/...  (branch overrides)
    const parts = u.pathname.split("/").filter(Boolean);
    const designIdx = parts.findIndex((p) => p === "design" || p === "file" || p === "board" || p === "slides");
    if (designIdx !== -1 && parts[designIdx + 1]) {
      out.fileKey = parts[designIdx + 1];
    }
    // branch key takes precedence as the effective file key
    const branchIdx = parts.findIndex((p) => p === "branch");
    if (branchIdx !== -1 && parts[branchIdx + 1]) {
      out.fileKey = parts[branchIdx + 1];
    }
    // node id: ?node-id=10503-322  →  10503:322
    const nodeParam = u.searchParams.get("node-id");
    if (nodeParam) out.nodeId = nodeParam.replace(/-/g, ":");
  } catch {
    /* not a URL */
  }
  return out;
}

const isFigmaUrl = (s) => typeof s === "string" && /figma\.com\//i.test(s);

// ── CLI parsing ───────────────────────────────────────────────────────────────
function parseArgs(argv) {
  const args = argv.slice(2);
  const opts = {
    token:       null,
    url:         null,
    fileKey:     null,
    nodeId:      null,
    mapFile:     null,
    outDir:      path.join(process.cwd(), "assets", "images"),
    format:      "png",
    scale:       2,
    concurrency: 8,
    flat:        false,
    dryRun:      false,
    overwrite:   false,
  };

  const positional = [];
  for (let i = 0; i < args.length; i++) {
    const a = args[i];
    switch (a) {
      case "--url":         opts.url         = args[++i]; break;
      case "--file":        opts.fileKey     = args[++i]; break;
      case "--node":        opts.nodeId      = args[++i]; break;
      case "--map":         opts.mapFile     = args[++i]; break;
      case "--out":         opts.outDir      = path.resolve(args[++i]); break;
      case "--format":      opts.format      = args[++i]; break;
      case "--scale":       opts.scale       = Number(args[++i]); break;
      case "--concurrency": opts.concurrency = Number(args[++i]); break;
      case "--flat":        opts.flat        = true; break;
      case "--dry-run":     opts.dryRun      = true; break;
      case "--overwrite":   opts.overwrite   = true; break;
      case "-h":
      case "--help":        opts.help        = true; break;
      default:
        if (a.startsWith("--")) console.warn(`Unknown option: ${a}`);
        else positional.push(a);
    }
  }

  // Positional args: token first, then any Figma URL
  for (const p of positional) {
    if (isFigmaUrl(p)) { opts.url = opts.url || p; }
    else if (!opts.token) { opts.token = p; }
  }

  // Resolve token from env if not given
  opts.token = opts.token || process.env.FIGMA_TOKEN;

  // If a URL was provided, parse file key + node id from it (CLI flags win)
  if (opts.url) {
    const parsed = parseFigmaUrl(opts.url);
    opts.fileKey = opts.fileKey || parsed.fileKey;
    opts.nodeId  = opts.nodeId  || parsed.nodeId;
  }

  return opts;
}

// ── Colours ───────────────────────────────────────────────────────────────────
const isTTY = process.stdout.isTTY;
const c = {
  green:  isTTY ? "\x1b[32m" : "",
  red:    isTTY ? "\x1b[31m" : "",
  yellow: isTTY ? "\x1b[33m" : "",
  cyan:   isTTY ? "\x1b[36m" : "",
  bold:   isTTY ? "\x1b[1m"  : "",
  dim:    isTTY ? "\x1b[2m"  : "",
  reset:  isTTY ? "\x1b[0m"  : "",
};

// ── HTTP helpers ──────────────────────────────────────────────────────────────
function httpsGet(url, headers = {}, timeout = 180000) {
  return new Promise((resolve, reject) => {
    const req = https.get(url, { headers }, (res) => {
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        return httpsGet(res.headers.location, {}, timeout).then(resolve).catch(reject);
      }
      const chunks = [];
      res.on("data", (d) => chunks.push(d));
      res.on("end", () => resolve({ status: res.statusCode, body: Buffer.concat(chunks).toString("utf8") }));
      res.on("error", reject);
    });
    req.on("error", reject);
    req.setTimeout(timeout, () => { req.destroy(); reject(new Error("Request timeout")); });
  });
}

function httpsDownload(url, destPath, timeout = 120000) {
  return new Promise((resolve, reject) => {
    const attempt = (targetUrl) => {
      https.get(targetUrl, (res) => {
        if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
          res.resume();
          return attempt(res.headers.location);
        }
        if (res.statusCode !== 200) {
          res.resume();
          return reject(new Error(`HTTP ${res.statusCode}`));
        }
        const tmp = destPath + ".tmp";
        const file = fs.createWriteStream(tmp);
        res.pipe(file);
        file.on("finish", () =>
          file.close(() => {
            fs.rename(tmp, destPath, (err) => (err ? reject(err) : resolve(fs.statSync(destPath).size)));
          })
        );
        file.on("error", (e) => { fs.unlink(tmp, () => {}); reject(e); });
      }).on("error", reject).setTimeout(timeout, function () { this.destroy(); reject(new Error("Download timeout")); });
    };
    attempt(url);
  });
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// ── Figma API ─────────────────────────────────────────────────────────────────
// Figma's image-render endpoint can be slow (it renders nodes on demand), so we
// use a generous timeout and retry transient failures (timeouts / 429 / 5xx).
async function figmaGet(endpoint, token, { timeout = 180000, retries = 3 } = {}) {
  let lastErr;
  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      const res = await httpsGet(`${FIGMA_API}${endpoint}`, { "X-Figma-Token": token }, timeout);
      if (res.status === 401) throw new Error("401 Unauthorized — check your Figma token.");
      if (res.status === 403) throw new Error("403 Forbidden — token may lack access to this file.");
      if (res.status === 404) throw new Error("404 Not Found — check the file key or node ID.");
      if (res.status === 429 || res.status >= 500) {
        // Rate-limited or server error → retry with backoff
        throw new Error(`HTTP ${res.status} (retryable)`);
      }
      if (res.status !== 200) throw new Error(`Figma API error: HTTP ${res.status}\n${res.body}`);
      try { return JSON.parse(res.body); } catch { throw new Error("Failed to parse Figma API response."); }
    } catch (err) {
      lastErr = err;
      const retryable = /timeout|retryable|ECONNRESET|ETIMEDOUT|socket hang up|EAI_AGAIN|ENOTFOUND/i.test(err.message);
      // Don't retry auth/permission errors
      if (/401|403|404/.test(err.message)) throw err;
      if (attempt < retries && retryable) {
        const wait = 2000 * (attempt + 1); // 2s, 4s, 6s backoff
        console.log(`${c.yellow}retry ${attempt + 1}/${retries} in ${wait / 1000}s (${err.message})${c.reset}`);
        await sleep(wait);
        continue;
      }
      throw err;
    }
  }
  throw lastErr;
}

/** Resolve node IDs → CDN image URLs.
 *  Rendering many large nodes at once is the slowest part, so we use SMALL
 *  chunks (each call renders fewer nodes → finishes faster → far less likely
 *  to time out) and retry each chunk independently. */
async function resolveImageUrls(fileKey, entries, token, scale) {
  const byFormat = {};
  for (const e of entries) (byFormat[e.format] = byFormat[e.format] || []).push(e);

  const urlMap = {};
  for (const [fmt, group] of Object.entries(byFormat)) {
    const CHUNK = 5; // small chunks keep each render request fast & resilient
    for (let i = 0; i < group.length; i += CHUNK) {
      const chunk = group.slice(i, i + CHUNK);
      const ids = chunk.map((e) => e.nodeId).join(",");
      const part = `${Math.floor(i / CHUNK) + 1}/${Math.ceil(group.length / CHUNK)}`;
      process.stdout.write(`  → ${fmt.toUpperCase()} batch ${part} (${chunk.length} nodes)... `);
      try {
        const data = await figmaGet(`/images/${fileKey}?ids=${ids}&format=${fmt}&scale=${scale}`, token);
        if (data.err) { console.log(`${c.red}error: ${data.err}${c.reset}`); continue; }
        const images = data.images || {};
        let found = 0;
        for (const e of chunk) {
          const key = e.nodeId.replace(/:/g, "-");
          const url = images[key] || images[e.nodeId];
          if (url && url !== "null") { urlMap[e.nodeId] = url; found++; }
        }
        console.log(`${c.green}${found}/${chunk.length} resolved${c.reset}`);
      } catch (err) {
        // One bad chunk shouldn't kill the whole run — log and move on
        console.log(`${c.red}failed: ${err.message}${c.reset}`);
      }
    }
  }
  return urlMap;
}

/** Walk a Figma node tree and collect every node that carries an image fill */
async function autoDiscoverNodes(fileKey, nodeId, token, defaultFormat, flat) {
  process.stdout.write("  Fetching file tree... ");
  const endpoint = nodeId
    ? `/files/${fileKey}/nodes?ids=${nodeId}&depth=99`
    : `/files/${fileKey}?depth=99`;
  const data = await figmaGet(endpoint, token);

  let root;
  if (data.document) {
    root = data.document;
  } else if (data.nodes) {
    const keys = Object.keys(data.nodes);
    root = keys.length === 1
      ? data.nodes[keys[0]].document
      : { children: keys.map((k) => data.nodes[k].document) };
  } else {
    throw new Error("Unexpected Figma API response structure.");
  }
  console.log(`${c.green}OK${c.reset}`);

  const discovered = [];
  const seen = new Set();
  const usedNames = new Set();

  function pickFormat(node) {
    const name = (node.name || "").toLowerCase();
    if (name.endsWith(".svg")) return "svg";
    if (name.endsWith(".jpg") || name.endsWith(".jpeg")) return "jpg";
    if (name.endsWith(".png")) return "png";
    if (/\b(icon|logo|vector|svg)\b/i.test(node.name || "")) return "svg";
    return defaultFormat;
  }

  function makeName(node, fmt) {
    const base = (node.name || `node-${node.id}`)
      .replace(/\.[a-z0-9]+$/i, "")                 // strip existing extension
      .replace(/[<>:"/\\|?*\x00-\x1f]/g, "-")       // illegal filename chars
      .replace(/\s+/g, "-")
      .replace(/-+/g, "-")
      .replace(/^-|-$/g, "")
      .toLowerCase()
      .slice(0, 60) || "asset";

    if (flat) {
      // Clean name; disambiguate collisions with a counter
      let name = `${base}.${fmt}`;
      let n = 2;
      while (usedNames.has(name)) name = `${base}-${n++}.${fmt}`;
      usedNames.add(name);
      return name;
    }
    // Default: append node-id suffix so names are always unique
    return `${base}__${node.id.replace(/:/g, "-")}.${fmt}`;
  }

  function walk(node) {
    if (!node || seen.has(node.id)) return;
    seen.add(node.id);
    const fills = node.fills || [];
    const hasImageFill = fills.some((f) => f.type === "IMAGE" && f.visible !== false);
    const isImageLike  = ["RECTANGLE", "ELLIPSE", "VECTOR", "FRAME", "GROUP", "INSTANCE", "COMPONENT"].includes(node.type);
    if (hasImageFill && isImageLike) {
      const fmt = pickFormat(node);
      discovered.push({ filename: makeName(node, fmt), nodeId: node.id, format: fmt, nodeName: node.name });
    }
    if (node.children) node.children.forEach(walk);
  }

  walk(root);
  return discovered;
}

// ── Parallel runner ───────────────────────────────────────────────────────────
async function runParallel(tasks, concurrency) {
  const results = [];
  const queue = [...tasks];
  const workers = Array.from({ length: Math.min(concurrency, queue.length) }, async () => {
    while (queue.length) {
      const task = queue.shift();
      results.push(await Promise.resolve().then(task).catch((err) => ({ ok: false, error: err.message })));
    }
  });
  await Promise.all(workers);
  return results;
}

function prettyBytes(n) {
  if (n < 1024) return `${n} B`;
  if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
  return `${(n / 1024 / 1024).toFixed(2)} MB`;
}

// ── Usage ─────────────────────────────────────────────────────────────────────
function printUsage() {
  console.log(`
${c.bold}download-images.js${c.reset} — Universal Figma Asset Downloader

${c.bold}QUICK START${c.reset} ${c.dim}(just paste any Figma URL)${c.reset}
  node download-images.js <token> "<figma-url>"

  ${c.dim}# Downloads every image from whatever frame/page the URL points to${c.reset}
  node download-images.js figd_xxx \\
    "https://www.figma.com/design/ABC123/Site?node-id=10503-322"

${c.bold}OTHER WAYS TO RUN${c.reset}
  ${c.dim}# Separate file key + node id${c.reset}
  node download-images.js figd_xxx --file ABC123 --node 10503:322

  ${c.dim}# Whole file (every page)${c.reset}
  node download-images.js figd_xxx --file ABC123

  ${c.dim}# JSON map for exact filenames${c.reset}
  node download-images.js figd_xxx --file ABC123 --map nodes.json

  ${c.dim}# Preview without downloading${c.reset}
  node download-images.js figd_xxx "<url>" --dry-run

${c.bold}OPTIONS${c.reset}
  --url <url>        Figma URL (file key + node parsed automatically)
  --file <key>       Figma file key
  --node <id>        Node/frame/page ID  ${c.dim}(omit = whole file)${c.reset}
  --map <file>       JSON map: filename → { nodeId, format }
  --out <dir>        Output directory     ${c.dim}(default: ./assets/images)${c.reset}
  --format <fmt>     Default format svg|png|jpg  ${c.dim}(default: png)${c.reset}
  --scale <n>        Export scale 1-4     ${c.dim}(default: 2)${c.reset}
  --concurrency <n>  Parallel downloads   ${c.dim}(default: 8)${c.reset}
  --flat             Clean filenames without node-id suffix
  --dry-run          Print URLs, don't download
  --overwrite        Re-download existing files

${c.bold}ENV${c.reset}
  FIGMA_TOKEN        Set the token via environment variable

${c.bold}HOW TO GET A TOKEN${c.reset}
  Figma → Settings → Security → Personal access tokens → Generate new token
  (Needs at least "File content: Read" scope.)
`);
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {
  const opts = parseArgs(process.argv);

  if (opts.help || (!opts.token && !opts.fileKey && !opts.url)) { printUsage(); process.exit(0); }
  if (!opts.token) {
    console.error(`\n${c.red}ERROR:${c.reset} Figma token required (first argument or FIGMA_TOKEN env var).\n`);
    process.exit(1);
  }
  if (!opts.fileKey) {
    console.error(`\n${c.red}ERROR:${c.reset} No file key. Pass a Figma URL, or use --file <key>.\n`);
    process.exit(1);
  }

  console.log(`\n${c.bold}╔══════════════════════════════════════════╗`);
  console.log(`║       Figma Asset Downloader             ║`);
  console.log(`╚══════════════════════════════════════════╝${c.reset}\n`);
  console.log(`  File     : ${c.cyan}${opts.fileKey}${c.reset}`);
  console.log(`  Node     : ${c.cyan}${opts.nodeId || "(whole file)"}${c.reset}`);
  if (opts.mapFile) console.log(`  Map file : ${c.cyan}${opts.mapFile}${c.reset}`);
  console.log(`  Output   : ${c.cyan}${opts.outDir}${c.reset}`);
  console.log(`  Format   : ${opts.format}  |  Scale: ${opts.scale}x  |  Concurrency: ${opts.concurrency}  |  Names: ${opts.flat ? "flat" : "unique"}`);
  if (opts.dryRun) console.log(`  ${c.yellow}DRY RUN — files will not be saved${c.reset}`);
  console.log();

  // ── Build the entries list ─────────────────────────────────────────────────
  let entries = [];

  if (opts.mapFile) {
    console.log(`📋  Loading map file: ${opts.mapFile}\n`);
    let raw;
    try { raw = JSON.parse(fs.readFileSync(path.resolve(opts.mapFile), "utf8")); }
    catch (e) { console.error(`${c.red}ERROR reading map file: ${e.message}${c.reset}`); process.exit(1); }
    entries = Object.entries(raw)
      .filter(([k]) => !k.startsWith("_"))   // skip _comment / _usage keys
      .map(([filename, v]) => ({ filename, nodeId: v.nodeId, format: v.format || opts.format }));
    console.log(`  ${entries.length} entries loaded.\n`);
  } else {
    console.log(`🔍  Auto-discovering image nodes...\n`);
    entries = await autoDiscoverNodes(opts.fileKey, opts.nodeId, opts.token, opts.format, opts.flat);
    console.log(`  Found ${c.bold}${entries.length}${c.reset} image node(s).\n`);
    if (entries.length === 0) {
      console.log(`${c.yellow}No image fills found in that node. Try a parent frame, the whole file (omit --node), or --map mode.${c.reset}\n`);
      process.exit(0);
    }
  }

  // ── Skip existing files unless --overwrite ─────────────────────────────────
  if (!opts.overwrite && !opts.dryRun) {
    const before = entries.length;
    entries = entries.filter((e) => !fs.existsSync(path.join(opts.outDir, e.filename)));
    const skipped = before - entries.length;
    if (skipped > 0) console.log(`  ${c.dim}Skipping ${skipped} existing file(s). Use --overwrite to replace.${c.reset}\n`);
    if (entries.length === 0) { console.log(`${c.green}All files already present.${c.reset}\n`); process.exit(0); }
  }

  // ── Resolve URLs ───────────────────────────────────────────────────────────
  console.log(`🌐  Resolving download URLs...\n`);
  let urlMap;
  try { urlMap = await resolveImageUrls(opts.fileKey, entries, opts.token, opts.scale); }
  catch (e) { console.error(`\n${c.red}${e.message}${c.reset}\n`); process.exit(1); }

  const resolved   = entries.filter((e) => urlMap[e.nodeId]);
  const unresolved = entries.filter((e) => !urlMap[e.nodeId]);
  console.log(`\n  Resolved: ${c.green}${resolved.length}${c.reset}  |  Failed: ${c.red}${unresolved.length}${c.reset}\n`);
  unresolved.forEach((e) => console.log(`  ${c.yellow}⚠  No URL: ${e.filename} (${e.nodeId})${c.reset}`));
  if (unresolved.length) console.log();

  // ── Dry run ────────────────────────────────────────────────────────────────
  if (opts.dryRun) {
    console.log(`${c.bold}Resolved URLs (dry run):${c.reset}\n`);
    for (const e of resolved) {
      console.log(`  ${c.green}${e.filename}${c.reset}`);
      console.log(`  ${c.dim}${urlMap[e.nodeId]}${c.reset}\n`);
    }
    process.exit(0);
  }

  // ── Download ───────────────────────────────────────────────────────────────
  fs.mkdirSync(opts.outDir, { recursive: true });
  console.log(`⬇️   Downloading ${resolved.length} file(s)...\n`);

  const t0 = Date.now();
  const tasks = resolved.map((e) => async () => {
    const dest = path.join(opts.outDir, e.filename);
    try {
      const bytes = await httpsDownload(urlMap[e.nodeId], dest);
      console.log(`  ${c.green}✓${c.reset} ${e.filename.padEnd(48)} ${c.dim}${prettyBytes(bytes)}${c.reset}`);
      return { filename: e.filename, ok: true, bytes };
    } catch (err) {
      console.log(`  ${c.red}✗${c.reset} ${e.filename.padEnd(48)} ${err.message}`);
      return { filename: e.filename, ok: false, error: err.message };
    }
  });

  const results = await runParallel(tasks, opts.concurrency);
  const elapsed = ((Date.now() - t0) / 1000).toFixed(1);

  // ── Summary ────────────────────────────────────────────────────────────────
  const ok = results.filter((r) => r.ok);
  const failed = results.filter((r) => !r.ok);
  const totalBytes = ok.reduce((s, r) => s + (r.bytes || 0), 0);

  console.log(`\n${"─".repeat(54)}`);
  console.log(`  ${c.green}✓ Downloaded : ${ok.length} file(s)  (${prettyBytes(totalBytes)})${c.reset}`);
  if (failed.length)     console.log(`  ${c.red}✗ Failed     : ${failed.length}${c.reset}`);
  if (unresolved.length) console.log(`  ${c.yellow}⚠  Unresolved : ${unresolved.length}${c.reset}`);
  console.log(`  ⏱  Time      : ${elapsed}s`);
  console.log(`  📁 Saved to  : ${opts.outDir}`);
  console.log(`${"─".repeat(54)}\n`);

  if (failed.length) {
    console.log(`${c.yellow}Failed files:${c.reset}`);
    failed.forEach((r) => console.log(`  • ${r.filename}: ${r.error}`));
    console.log();
  }
  process.exit(failed.length ? 1 : 0);
}

main().catch((err) => {
  console.error(`\n${c.red}Fatal error: ${err.message}${c.reset}\n`);
  if (process.env.DEBUG) console.error(err.stack);
  process.exit(1);
});
