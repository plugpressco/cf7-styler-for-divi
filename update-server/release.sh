#!/usr/bin/env bash
# Publish a CF7 Mate Pro release: upload zip to R2 + write manifest to KV.
#
# Usage: ./release.sh <version>
#   e.g.  ./release.sh 1.0.1
#
# Expects the zip at ../cf7-mate-pro-<version>.zip and the changelog block for
# this version in ../changelog-pro.txt (between "= <version> =" and the next "= ").

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────
SLUG="cf7-mate-pro"
TESTED="6.9"
REQUIRES="6.0"
REQUIRES_PHP="7.4"
DESCRIPTION="Pro features for CF7 Mate."
# ──────────────────────────────────────────────────────────────────────────────

if [[ $# -ne 1 ]]; then
    echo "usage: $0 <version>" >&2
    exit 1
fi

VERSION="$1"
PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ZIP_LOCAL="${PLUGIN_DIR}/${SLUG}-${VERSION}.zip"
ZIP_KEY="${SLUG}/${SLUG}-${VERSION}.zip"
CHANGELOG_FILE="${PLUGIN_DIR}/changelog-pro.txt"

if [[ ! -f "$ZIP_LOCAL" ]]; then
    echo "error: zip not found at $ZIP_LOCAL" >&2
    echo "       run 'npm run package:pro' from the plugin root first" >&2
    exit 1
fi

if [[ ! -f "$CHANGELOG_FILE" ]]; then
    echo "error: changelog not found at $CHANGELOG_FILE" >&2
    exit 1
fi

# Extract the changelog block for this version. Matches headers like
# "= 1.0.1 =" or "= 1.0.1 (anything) =" — line must start with "= <version> "
# (followed by " =" or " (...)") and capture until the next "= " header.
CHANGELOG="$(awk -v ver="$VERSION" '
    $0 ~ "^= " ver "( |$)" { capture = 1; next }
    capture && /^= [0-9]/ { exit }
    capture { print }
' "$CHANGELOG_FILE" | awk 'NF { last = NR; lines[NR] = $0; next } { lines[NR] = "" }
END { for (i = 1; i <= last; i++) print lines[i] }')"

if [[ -z "$CHANGELOG" ]]; then
    echo "warning: no changelog block found for version $VERSION in $CHANGELOG_FILE" >&2
    echo "         continuing with empty changelog — Ctrl-C now to abort" >&2
    sleep 2
fi

# Build the manifest JSON via python3 to handle quoting/escaping cleanly.
MANIFEST="$(VERSION="$VERSION" ZIP_KEY="$ZIP_KEY" TESTED="$TESTED" REQUIRES="$REQUIRES" \
    REQUIRES_PHP="$REQUIRES_PHP" DESCRIPTION="$DESCRIPTION" CHANGELOG="$CHANGELOG" \
    python3 -c '
import json, os
print(json.dumps({
    "version":      os.environ["VERSION"],
    "zip_key":      os.environ["ZIP_KEY"],
    "tested":       os.environ["TESTED"],
    "requires":     os.environ["REQUIRES"],
    "requires_php": os.environ["REQUIRES_PHP"],
    "description":  os.environ["DESCRIPTION"],
    "changelog":    os.environ["CHANGELOG"].strip(),
}))
')"

cd "$(dirname "$0")"

echo "▸ Uploading $ZIP_LOCAL → R2 as $ZIP_KEY"
npx wrangler r2 object put "cf7mate-plugin-zips/${ZIP_KEY}" --file="$ZIP_LOCAL" --remote

echo ""
echo "▸ Writing manifest to KV ($SLUG):"
echo "$MANIFEST" | python3 -m json.tool
echo "$MANIFEST" | npx wrangler kv key put --binding RELEASES --remote "$SLUG" --path /dev/stdin

echo ""
echo "▸ Verifying /info.json …"
sleep 2
curl -s "https://updates.cf7mate.com/info.json" | python3 -m json.tool

echo ""
echo "✓ Released $SLUG $VERSION"
