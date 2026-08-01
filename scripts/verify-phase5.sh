#!/usr/bin/env bash
# Phase 5 admin CRUD verification (curl). Sanctum SPA cookie auth flow.
#
# Mirrors scripts/verify-auth.sh: every request carries an Origin header that
# matches SANCTUM_STATEFUL_DOMAINS, and every write carries the X-XSRF-TOKEN
# header read back (URL-decoded) from the cookie jar.
#
# No credentials are baked in. Provide EMAIL/PASSWORD as env vars with no
# default — the script refuses to run otherwise:
#
#   EMAIL=superadmin@gm-bridge.test PASSWORD=placeholder-superadmin-123 \
#   PRO_EMAIL=admin@gm-bridge.test PRO_PASSWORD=placeholder-admin-123 \
#   bash scripts/verify-phase5.sh
#
set -euo pipefail

BASE_URL="${BASE_URL:-http://talent-marketplace-api.test}"
ORIGIN="${ORIGIN:-http://localhost:3000}"
EMAIL="${EMAIL:?EMAIL env var required (the superadmin email)}"
PASSWORD="${PASSWORD:?PASSWORD env var required (the superadmin password)}"
# A plain (non-super) admin, used only to assert the superadmin gate returns 403.
PRO_EMAIL="${PRO_EMAIL:?PRO_EMAIL env var required (a plain admin email)}"
PRO_PASSWORD="${PRO_PASSWORD:?PRO_PASSWORD env var required (a plain admin password)}"

JAR="$(mktemp)"
cleanup() { rm -f "$JAR"; }
trap cleanup EXIT

LOG_FILE="storage/logs/laravel.log"

# Resolve + URL-decode XSRF-TOKEN from the cookie jar (python3, falling back to python).
xsrf_from_jar() {
  awk -F'\t' '$6 == "XSRF-TOKEN" { print $7; exit }' "$JAR" \
    | python3 -c "import sys, urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))" 2>/dev/null \
    || awk -F'\t' '$6 == "XSRF-TOKEN" { print $7; exit }' "$JAR" \
    | python -c "import sys, urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))"
}

# Authenticate as the given credentials into a fresh cookie jar, print a marker.
login() {
  local email="$1" pass="$2"
  curl -sS -o /dev/null -c "$JAR" -b "$JAR" "$BASE_URL/sanctum/csrf-cookie" -H "Origin: $ORIGIN"
  local xsrf
  xsrf="$(xsrf_from_jar)"
  curl -sS -o /dev/null -c "$JAR" -b "$JAR" -X POST "$BASE_URL/api/admin/login" \
    -H "Origin: $ORIGIN" -H "Content-Type: application/json" -H "Accept: application/json" \
    -H "X-XSRF-TOKEN: $xsrf" \
    -d "{\"email\":\"$email\",\"password\":\"$pass\"}"
}

# Authenticated request: GET.
aget() {
  curl -sS -i -c "$JAR" -b "$JAR" "$BASE_URL$1" \
    -H "Origin: $ORIGIN" -H "Accept: application/json" \
    -H "X-XSRF-TOKEN: $(xsrf_from_jar)"
}

# Authenticated request: write verb (POST/PUT/PATCH/DELETE), with optional -d / -F.
awrite() {
  local method="$1" path="$2"; shift 2
  curl -sS -i -c "$JAR" -b "$JAR" -X "$method" "$BASE_URL$path" \
    -H "Origin: $ORIGIN" -H "Accept: application/json" \
    -H "X-XSRF-TOKEN: $(xsrf_from_jar)" "$@"
}

sep() { echo; echo "======================================================================"; echo "$1"; echo "======================================================================"; }

# ---------------------------------------------------------------- login (superadmin)
sep "LOGIN as superadmin ($EMAIL)"
login "$EMAIL" "$PASSWORD"
echo "logged in."

# ---------------------------------------------------------------- professionals CRUD
sep "1. PROFESSIONALS — index (admin sees all, incl. inactive)"
aget /api/admin/professionals | head -n 1

sep "2. PROFESSIONALS — store (with photo upload)"
PHOTO="$(mktemp --suffix=.png)"
printf '\x89PNG\r\n\x1a\n' > "$PHOTO"
STORE_RESP="$(awrite POST /api/admin/professionals \
  -H "Content-Type: multipart/form-data" \
  -F "name=Phase Five Tester" \
  -F "role_title=QA Engineer" \
  -F "bio=Created by the Phase 5 verification script." \
  -F 'skills[]=Manual Testing' \
  -F 'skills[]=Automation' \
  -F "service_id=1" \
  -F "status=active" \
  -F "is_featured=0" \
  -F "photo=@$PHOTO;type=image/png")"
echo "$STORE_RESP" | head -n 1
echo "$STORE_RESP" | tail -n 1   # JSON body with id + photo_url
PRO_ID="$(echo "$STORE_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null \
  || echo "$STORE_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['id'])")"
PHOTO_URL="$(echo "$STORE_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['photo_url'])" 2>/dev/null \
  || echo "$STORE_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['photo_url'])")"
echo "created professional id=$PRO_ID photo_url=$PHOTO_URL"

sep "3. PROFESSIONALS — update (PUT by id; slug must be unchanged)"
UPDATE_RESP="$(awrite PUT /api/admin/professionals/$PRO_ID \
  -H "Content-Type: application/json" \
  -d '{"role_title":"Senior QA Engineer","is_featured":true}')"
echo "$UPDATE_RESP" | head -n 1
echo "$UPDATE_RESP" | tail -n 1
rm -f "$PHOTO"

sep "4. PROFESSIONALS — destroy (204, photo file removed)"
awrite DELETE /api/admin/professionals/$PRO_ID | head -n 1

# ---------------------------------------------------------------- portfolio CRUD (3 media types)
sep "5a. PORTFOLIO — store IMAGE"
IMG="$(mktemp --suffix=.png)"; printf '\x89PNG\r\n\x1a\n' > "$IMG"
P_IMG_RESP="$(awrite POST /api/admin/portfolio \
  -H "Content-Type: multipart/form-data" \
  -F "title=Img Item" -F "description=d" -F "media_type=image" \
  -F "professional_id=1" -F "is_featured=0" \
  -F "image=@$IMG;type=image/png")"
echo "$P_IMG_RESP" | head -n 1
echo "$P_IMG_RESP" | tail -n 1
P_IMG_ID="$(echo "$P_IMG_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null \
  || echo "$P_IMG_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['id'])")"
rm -f "$IMG"

sep "5b. PORTFOLIO — update IMAGE (replace file)"
IMG2="$(mktemp --suffix=.png)"; printf '\x89PNG\r\n\x1a\n' > "$IMG2"
awrite POST /api/admin/portfolio -H "Content-Type: multipart/form-data" >/dev/null \
  || true # noop placeholder to keep flow readable
awrite PUT /api/admin/portfolio/$P_IMG_ID \
  -H "Content-Type: multipart/form-data" \
  -F "title=Img Item (updated)" -F "image=@$IMG2;type=image/png" | head -n 1
rm -f "$IMG2"

sep "5c. PORTFOLIO — destroy IMAGE"
awrite DELETE /api/admin/portfolio/$P_IMG_ID | head -n 1

sep "6. PORTFOLIO — store/update/destroy PDF"
PDF="$(mktemp --suffix=.pdf)"; printf '%%PDF-1.4\n%%EOF' > "$PDF"
P_PDF_RESP="$(awrite POST /api/admin/portfolio \
  -H "Content-Type: multipart/form-data" \
  -F "title=Pdf Item" -F "media_type=pdf" -F "professional_id=1" -F "is_featured=0" \
  -F "pdf=@$PDF;type=application/pdf")"
echo "$P_PDF_RESP" | head -n 1; echo "$P_PDF_RESP" | tail -n 1
P_PDF_ID="$(echo "$P_PDF_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null \
  || echo "$P_PDF_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['id'])")"
awrite PUT /api/admin/portfolio/$P_PDF_ID -H "Content-Type: application/json" -d '{"title":"Pdf Item (updated)"}' | head -n 1
awrite DELETE /api/admin/portfolio/$P_PDF_ID | head -n 1
rm -f "$PDF"

sep "7. PORTFOLIO — store/update/destroy YOUTUBE (no file)"
P_YT_RESP="$(awrite POST /api/admin/portfolio \
  -H "Content-Type: application/json" \
  -d '{"title":"YT Item","media_type":"youtube","professional_id":1,"is_featured":0,"youtube_url":"https://www.youtube.com/watch?v=dQw4w9WgXcQ"}')"
echo "$P_YT_RESP" | head -n 1; echo "$P_YT_RESP" | tail -n 1
P_YT_ID="$(echo "$P_YT_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null \
  || echo "$P_YT_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['id'])")"
awrite PUT /api/admin/portfolio/$P_YT_ID -H "Content-Type: application/json" -d '{"title":"YT Item (updated)"}' | head -n 1
awrite DELETE /api/admin/portfolio/$P_YT_ID | head -n 1

# ---------------------------------------------------------------- role gating
sep "8. ROLE GATING — plain admin gets 403 on /api/admin/admins"
login "$PRO_EMAIL" "$PRO_PASSWORD"
awrite GET /api/admin/admins 2>&1 | head -n 1 || aget /api/admin/admins | head -n 1

sep "9. SUPERADMIN — admins index/store (re-login as superadmin)"
login "$EMAIL" "$PASSWORD"
aget /api/admin/admins | head -n 1
ADMIN_RESP="$(awrite POST /api/admin/admins \
  -H "Content-Type: application/json" \
  -d '{"name":"Temp Admin","email":"temp-admin-phase5@gm-bridge.test","password":"temp-pass-12345","password_confirmation":"temp-pass-12345","role":"admin"}')"
echo "$ADMIN_RESP" | head -n 1; echo "$ADMIN_RESP" | tail -n 1
TEMP_ADMIN_ID="$(echo "$ADMIN_RESP" | tail -n 1 | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null \
  || echo "$ADMIN_RESP" | tail -n 1 | python -c "import sys,json; print(json.load(sys.stdin)['data']['id'])")"
# clean up the temp admin we just created
awrite DELETE /api/admin/admins/$TEMP_ADMIN_ID | head -n 1

# ---------------------------------------------------------------- revalidation log
sep "10. REVALIDATION — confirm webhook fired (expect a logged failure/no-op since no Next.js endpoint yet)"
echo "(last lines of $LOG_FILE mentioning revalidation):"
grep -iE "revalidation|revalidate" "$LOG_FILE" | tail -n 8 || echo "(no revalidation log lines found)"

echo
sep "DONE"
echo "Review the status codes above. Expected: 200/201 on create+list, 200 on update, 204 on delete,"
echo "403 when a plain admin hits /api/admin/admins, and at least one revalidation log line."
