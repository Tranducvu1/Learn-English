#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-http://127.0.0.1:8000}"
FAIL=0

ok() { echo "OK: $1"; }
fail() { echo "FAIL: $1 ${2:-}"; FAIL=$((FAIL+1)); }

echo "=== HanViet Laravel smoke test ($BASE) ==="

H=$(curl -sf "$BASE/api/health")
echo "$H" | grep -q '"ok":true' && ok health || fail health

B=$(curl -sf "$BASE/api/v1/bootstrap")
echo "$B" | php -r '
$j=json_decode(stream_get_contents(STDIN),true);
exit(
  count($j["lessons"]["levels"]??[])===6 &&
  count($j["vocabulary"]["words"]??[])===1200 &&
  count($j["quizzes"]["quizzes"]??[])===86 &&
  !empty($j["lessons"]["levels"][0]["lessons"][0]["vocabIds"]) &&
  isset($j["quizzes"]["quizzes"][0]["questions"][0]["correct"])
?0:1);
' && ok bootstrap || fail bootstrap

EMAIL="smoke_$(date +%s)@hanviet.local"
REG=$(curl -sf -X POST "$BASE/api/v1/auth/register" \
  -H 'Content-Type: application/json' \
  -d "{\"name\":\"Smoke\",\"email\":\"$EMAIL\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}")
TOKEN=$(echo "$REG" | php -r 'echo json_decode(stream_get_contents(STDIN),true)["token"]??"";')
[[ -n "$TOKEN" ]] && ok register || fail register

curl -sf -H "Authorization: Bearer $TOKEN" "$BASE/api/v1/me/progress" | grep -q '"settings"' \
  && ok progress || fail progress

curl -sf -X POST -H "Authorization: Bearer $TOKEN" "$BASE/api/v1/premium/demo" | grep -q '"isPremium":true' \
  && ok premium_demo || fail premium_demo

curl -sf -X POST -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"message":"你好"}' "$BASE/api/v1/ai/tutor/chat" | grep -q '"reply"' \
  && ok ai_chat || fail ai_chat

curl -sf -o /dev/null -w '' "$BASE/" && ok blade_home || fail blade_home
INDEX=$(curl -sf "$BASE/")
echo "$INDEX" | grep -q 'HANVIET_CONFIG' && ok blade_config || fail blade_config
echo "$INDEX" | grep -q 'hanviet-api' && ok blade_meta || fail blade_meta
curl -sf "$BASE/css/style.css" | head -c 100 | grep -q '.' && ok css || fail css
curl -sf "$BASE/js/api.js" | grep -q 'HanVietAPI' && ok api_js || fail api_js

if [[ $FAIL -eq 0 ]]; then
  echo "=== ALL PASSED ==="
  exit 0
else
  echo "=== $FAIL FAILED ==="
  exit 1
fi
