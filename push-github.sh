#!/bin/bash
# Chạy 1 lần để đưa code lên GitHub và kích hoạt deploy
# Usage: ./push-github.sh YOUR_GITHUB_USERNAME YOUR_REPO_NAME

set -e
cd "$(dirname "$0")"

USER="${1:-}"
REPO="${2:-learn-chinese}"

if [ -z "$USER" ]; then
  echo "Cách dùng: ./push-github.sh TEN_GITHUB [ten-repo]"
  echo "Ví dụ:   ./push-github.sh myname learn-chinese"
  exit 1
fi

REMOTE="https://github.com/${USER}/${REPO}.git"

echo "→ Repo: $REMOTE"
echo "→ Tạo repo trên GitHub trước: https://github.com/new (tên: $REPO)"
read -p "Đã tạo repo? Enter để tiếp..."

git init -b main 2>/dev/null || true
git add .
git status --short | head -30
echo "..."
git commit -m "Deploy: web học tiếng Trung 汉越学堂" || echo "(đã commit rồi)"
git remote remove origin 2>/dev/null || true
git remote add origin "$REMOTE"
git push -u origin main

echo ""
echo "✓ Đã push! Tiếp theo:"
echo "  1. GitHub → repo → Settings → Pages → Source: GitHub Actions"
echo "  2. Tab Actions → đợi workflow xanh"
echo "  3. Mở: https://${USER}.github.io/${REPO}/"
