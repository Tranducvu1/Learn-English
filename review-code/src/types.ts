export type ReviewSeverity = "info" | "warning" | "error" | "critical";

export type ReviewFinding = {
  file: string;
  line?: number;
  severity: ReviewSeverity;
  rule: string;
  message: string;
  suggestion?: string;
};

export type ReviewRule = {
  id: string;
  check: (file: string, content: string, lines: string[]) => ReviewFinding[];
};
