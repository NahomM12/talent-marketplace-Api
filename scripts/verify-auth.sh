#!/usr/bin/env bash
# Manual Sanctum SPA cookie-auth check (curl). Requires Origin matching SANCTUM_STATEFUL_DOMAINS.
set -euo pipefail

BASE_URL="${BASE_URL:-http://talent-marketplace-api.test}"
ORIGIN="${ORIGIN:-http://localhost:3000}"
EMAIL="${EMAIL:-superadmin@gm-bridge.test}"
PASSWORD="${PASSWORD:-placeholder-superadmin-123}"
JAR="$(mktemp)"

cleanup() {
  rm -f "$JAR"
}
trap cleanup EXIT

xsrf_from_jar() {
  awk -F'\t' '$6 == "XSRF-TOKEN" { print $7; exit }' "$JAR" | python3 -c "import sys, urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))" 2>/dev/null \
    || awk -F'\t' '$6 == "XSRF-TOKEN" { print $7; exit }' "$JAR" | python -c "import sys, urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))"
}

echo "=== GET /sanctum/csrf-cookie ==="
curl -sS -i -c "$JAR" -b "$JAR" "$BASE_URL/sanctum/csrf-cookie" -H "Origin: $ORIGIN" | head -n 12

XSRF="$(xsrf_from_jar)"
if [[ -z "${XSRF:-}" ]]; then
  echo "Could not read XSRF-TOKEN from cookie jar." >&2
  exit 1
fi

echo
echo "=== POST /api/admin/login (valid) ==="
curl -sS -i -c "$JAR" -b "$JAR" -X POST "$BASE_URL/api/admin/login" \
  -H "Origin: $ORIGIN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}"

echo
echo
echo "=== POST /api/admin/login (invalid password) ==="
curl -sS -i -c "$JAR" -b "$JAR" -X POST "$BASE_URL/api/admin/login" \
  -H "Origin: $ORIGIN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $(xsrf_from_jar)" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"wrong-password\"}"

echo
echo
echo "=== GET /api/admin/me (no session cookies) ==="
curl -sS -i "$BASE_URL/api/admin/me" -H "Origin: $ORIGIN" -H "Accept: application/json"

echo
echo
echo "=== GET /api/admin/me (authenticated) ==="
curl -sS -i -c "$JAR" -b "$JAR" "$BASE_URL/api/admin/me" \
  -H "Origin: $ORIGIN" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $(xsrf_from_jar)"

echo
