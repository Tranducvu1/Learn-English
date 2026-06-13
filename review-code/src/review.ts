import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { rules } from "./rules.js";
import type { ReviewFinding } from "./types.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, "../..");

const SCAN_DIRS = ["src", "js", "scripts"];
const EXTENSIONS = new Set([".ts", ".tsx", ".js", ".jsx", ".cjs", ".mjs"]);
const IGNORE_DIRS = new Set(["node_modules", "dist", "_site", ".git", "review-code"]);

const COLORS = {
  reset: "\x1b[0m",
  dim: "\x1b[2m",
  cyan: "\x1b[36m",
  green: "\x1b[32m",
  yellow: "\x1b[33m",
  red: "\x1b[31m",
  bold: "\x1b[1m",
};

function severityColor(severity: ReviewFinding["severity"]) {
  switch (severity) {
    case "critical":
    case "error":
      return COLORS.red;
    case "warning":
      return COLORS.yellow;
    default:
      return COLORS.cyan;
  }
}

function severityRank(severity: ReviewFinding["severity"]) {
  return { critical: 0, error: 1, warning: 2, info: 3 }[severity];
}

async function collectFiles(dir: string, root = PROJECT_ROOT): Promise<string[]> {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  const files: string[] = [];

  for (const entry of entries) {
    if (IGNORE_DIRS.has(entry.name)) continue;

    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...await collectFiles(absolute, root));
      continue;
    }

    if (!EXTENSIONS.has(path.extname(entry.name))) continue;
    files.push(path.relative(root, absolute).replace(/\\/g, "/"));
  }

  return files.sort();
}

async function reviewFile(relativePath: string): Promise<ReviewFinding[]> {
  const absolute = path.join(PROJECT_ROOT, relativePath);
  const content = await fs.readFile(absolute, "utf8");
  const lines = content.split("\n");
  const findings: ReviewFinding[] = [];

  for (const rule of rules) {
    findings.push(...rule.check(relativePath, content, lines));
  }

  return findings;
}

function printFinding(finding: ReviewFinding) {
  const color = severityColor(finding.severity);
  const loc = finding.line ? `:${finding.line}` : "";
  console.log(
    `${color}  [${finding.severity}]${COLORS.reset} ${COLORS.bold}${finding.file}${loc}${COLORS.reset} — ${finding.message}`,
  );
  if (finding.suggestion) {
    console.log(`${COLORS.dim}    → ${finding.suggestion}${COLORS.reset}`);
  }
}

function printSummary(findings: ReviewFinding[], fileCount: number, elapsedMs: number) {
  const counts = { critical: 0, error: 0, warning: 0, info: 0 };
  for (const f of findings) counts[f.severity] += 1;

  console.log(`\n${COLORS.bold}── Summary ──${COLORS.reset}`);
  console.log(`  Files scanned : ${fileCount}`);
  console.log(`  Findings      : ${findings.length}`);
  if (counts.critical) console.log(`  ${COLORS.red}critical: ${counts.critical}${COLORS.reset}`);
  if (counts.error) console.log(`  ${COLORS.red}error   : ${counts.error}${COLORS.reset}`);
  if (counts.warning) console.log(`  ${COLORS.yellow}warning : ${counts.warning}${COLORS.reset}`);
  if (counts.info) console.log(`  ${COLORS.cyan}info    : ${counts.info}${COLORS.reset}`);
  console.log(`  Time          : ${elapsedMs}ms`);

  const hasBlocker = counts.critical > 0 || counts.error > 0;
  if (hasBlocker) {
    console.log(`\n${COLORS.red}✗ Review failed — fix error/critical trước khi merge${COLORS.reset}`);
  } else if (findings.length === 0) {
    console.log(`\n${COLORS.green}✓ Clean — không có finding${COLORS.reset}`);
  } else {
    console.log(`\n${COLORS.green}✓ Review passed — chỉ còn warning/info${COLORS.reset}`);
  }
}

async function main() {
  const started = Date.now();
  const targetArg = process.argv[2];

  console.log(`${COLORS.cyan}${COLORS.bold}Code Review${COLORS.reset} — ${path.basename(PROJECT_ROOT)}\n`);

  let files: string[] = [];
  if (targetArg) {
    files = [targetArg.replace(/\\/g, "/")];
  } else {
    for (const dir of SCAN_DIRS) {
      const absolute = path.join(PROJECT_ROOT, dir);
      try {
        files.push(...await collectFiles(absolute));
      } catch {
        // dir không tồn tại — bỏ qua
      }
    }
  }

  if (files.length === 0) {
    console.error("Không tìm thấy file để review.");
    process.exit(1);
  }

  const allFindings: ReviewFinding[] = [];

  for (let i = 0; i < files.length; i += 1) {
    const file = files[i];
    process.stdout.write(`\r${COLORS.dim}Scanning [${i + 1}/${files.length}] ${file.padEnd(50)}${COLORS.reset}`);
    allFindings.push(...await reviewFile(file));
  }

  process.stdout.write("\r" + " ".repeat(80) + "\r");

  const sorted = allFindings.sort((a, b) => {
    const bySeverity = severityRank(a.severity) - severityRank(b.severity);
    if (bySeverity !== 0) return bySeverity;
    return a.file.localeCompare(b.file) || (a.line ?? 0) - (b.line ?? 0);
  });

  if (sorted.length === 0) {
    printSummary([], files.length, Date.now() - started);
    process.exit(0);
  }

  console.log(`${COLORS.bold}Findings:${COLORS.reset}\n`);
  for (const finding of sorted) {
    printFinding(finding);
  }

  printSummary(sorted, files.length, Date.now() - started);

  const exitCode = sorted.some((f) => f.severity === "critical" || f.severity === "error") ? 1 : 0;
  process.exit(exitCode);
}

main().catch((error) => {
  console.error("[review] fatal:", error instanceof Error ? error.message : error);
  process.exit(1);
});
