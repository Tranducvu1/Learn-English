import type { ReviewFinding, ReviewRule } from "./types.js";

function lineFindings(
  file: string,
  lines: string[],
  rule: string,
  pattern: RegExp,
  severity: ReviewFinding["severity"],
  message: (match: string, line: string) => string,
  suggestion?: string,
): ReviewFinding[] {
  const findings: ReviewFinding[] = [];
  lines.forEach((line, index) => {
    const match = line.match(pattern);
    if (match) {
      findings.push({
        file,
        line: index + 1,
        severity,
        rule,
        message: message(match[0], line.trim()),
        suggestion,
      });
    }
  });
  return findings;
}

export const rules: ReviewRule[] = [
  {
    id: "no-console",
    check(file, _content, lines) {
      if (file.startsWith("scripts/")) return [];
      return lineFindings(
        file,
        lines,
        "no-console",
        /\bconsole\.(log|debug|info|warn)\s*\(/,
        "warning",
        () => "console.* trong source — nên xóa trước khi deploy",
        "Dùng logger có flag dev hoặc xóa debug statement",
      );
    },
  },
  {
    id: "no-debugger",
    check(file, _content, lines) {
      return lineFindings(
        file,
        lines,
        "no-debugger",
        /\bdebugger\b/,
        "error",
        () => "Còn debugger statement",
        "Xóa debugger trước khi merge",
      );
    },
  },
  {
    id: "no-eval",
    check(file, _content, lines) {
      return lineFindings(
        file,
        lines,
        "no-eval",
        /\beval\s*\(/,
        "critical",
        () => "Dùng eval() — rủi ro bảo mật",
        "Refactor, tránh eval",
      );
    },
  },
  {
    id: "unsafe-innerhtml",
    check(file, _content, lines) {
      return lineFindings(
        file,
        lines,
        "unsafe-innerhtml",
        /\.innerHTML\s*=/,
        "warning",
        () => "Gán innerHTML — kiểm tra XSS nếu data từ user",
        "Dùng textContent hoặc sanitize input",
      );
    },
  },
  {
    id: "ts-ignore",
    check(file, _content, lines) {
      return lineFindings(
        file,
        lines,
        "ts-ignore",
        /@ts-(ignore|nocheck)/,
        "warning",
        () => "Bỏ qua type-check TypeScript",
        "Sửa type thay vì suppress",
      );
    },
  },
  {
    id: "todo-fixme",
    check(file, _content, lines) {
      return lineFindings(
        file,
        lines,
        "todo-fixme",
        /\/\/\s*(TODO|FIXME|HACK|XXX)\b/i,
        "info",
        (match) => `Còn ${match.toUpperCase()} chưa xử lý`,
      );
    },
  },
  {
    id: "large-file",
    check(file, _content, lines) {
      if (lines.length <= 400) return [];
      return [{
        file,
        severity: lines.length > 800 ? "warning" : "info",
        rule: "large-file",
        message: `File lớn (${lines.length} dòng) — khó maintain`,
        suggestion: "Tách module nhỏ hơn",
      }];
    },
  },
  {
    id: "duplicate-stack",
    check(file, content) {
      const findings: ReviewFinding[] = [];
      if (file === "js/srs.js" && content.includes("function review")) {
        findings.push({
          file,
          severity: "error",
          rule: "duplicate-stack",
          message: "Logic SRS trùng với src/lib/srs.ts — 2 stack song song (vanilla JS + React)",
          suggestion: "Gom về src/lib/srs.ts, js/ chỉ import bundle hoặc bỏ legacy",
        });
      }
      if (file === "js/app.js" && content.includes("const App =")) {
        findings.push({
          file,
          line: 1,
          severity: "warning",
          rule: "duplicate-stack",
          message: "js/app.js legacy song song với src/ React — dễ lệch feature",
          suggestion: "Migrate hết sang React hoặc document rõ phạm vi từng stack",
        });
      }
      return findings;
    },
  },
  {
    id: "any-type",
    check(file, _content, lines) {
      if (!file.endsWith(".ts") && !file.endsWith(".tsx")) return [];
      return lineFindings(
        file,
        lines,
        "any-type",
        /:\s*any\b|<any>|as any\b/,
        "info",
        () => "Dùng any — mất lợi ích type safety",
        "Khai báo type cụ thể",
      );
    },
  },
];
