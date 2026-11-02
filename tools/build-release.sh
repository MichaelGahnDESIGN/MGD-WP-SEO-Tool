#!/usr/bin/env bash
# Erstellt ein reproduzierbares WordPress-Plugin-ZIP ohne Git-Daten, Backups,
# Tests oder lokale Arbeitsdateien. Das Skript erwartet nur rsync, zip und shasum.
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
plugin_slug="mgd-wp-seo-tool"
main_file="$project_dir/$plugin_slug.php"
version="$(sed -nE 's/^ \* Version: +([0-9]+\.[0-9]+\.[0-9]+)$/\1/p' "$main_file")"

if [[ -z "$version" ]]; then
  echo "Keine gültige semantische Version im Plugin-Header gefunden." >&2
  exit 1
fi

release_dir="$project_dir/releases"
work_dir="$project_dir/.tmp/release-build/$version"
package="$release_dir/$plugin_slug-$version.zip"

rm -rf "$work_dir"
mkdir -p "$work_dir/$plugin_slug" "$release_dir"

rsync -a \
  --exclude '.git/' \
  --exclude '.tmp/' \
  --exclude 'releases/' \
  --exclude '.DS_Store' \
  --exclude 'tools/' \
  --exclude 'tests/' \
  --exclude 'docs/' \
  --exclude 'PLAYTEST/' \
  --exclude 'PlayTest/' \
  --exclude 'BACKUPS/' \
  --exclude '.playwright-cli/' \
  --exclude '.env*' \
  "$project_dir/" "$work_dir/$plugin_slug/"

rm -f "$package" "$package.sha256"
(
  cd "$work_dir"
  zip -qr "$package" "$plugin_slug"
)

unzip -t "$package" >/dev/null
( cd "$release_dir" && shasum -a 256 "$(basename "$package")" > "$(basename "$package").sha256" )
rm -rf "$work_dir"

echo "Release-Paket erstellt: $package"
