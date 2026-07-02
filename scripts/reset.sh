#!/usr/bin/env bash
#
# reset.sh — Reset the theme for a NEW project.
#
# Removes EVERY AI-generated `mbn-ai-` block/component (source + build), resets the
# components-skill registry, rebuilds, and lints. Optional flags also reset the built
# content, nav menus and preset (via WP-CLI). Core `mbn-` blocks and `src/shared/` are
# NEVER touched.
#
# This is DESTRUCTIVE and irreversible: it deletes files (and, with flags, DB data).
# It prints exactly what will be removed and asks for confirmation before doing anything
# (skip with --yes; preview only with --dry-run).
#
# Usage:
#   scripts/reset.sh [--content] [--menus] [--preset] [--media] [--all] [--yes] [--dry-run]
#
# Flags:
#   --content   Empty the built page(s), reset header/footer templates, drop seeded
#               testimonials (requires WP-CLI).
#   --menus     Delete AI-created nav menus and clear template menu meta (requires WP-CLI).
#   --preset    Reset the `mbn_settings` option to theme defaults (requires WP-CLI).
#   --media     LIST AI-uploaded media candidates for manual review (never auto-deleted —
#               media removal is dedup-sensitive; delete via the WP admin / MCP).
#   --all       --content --menus --preset --media.
#   --yes, -y   Skip the confirmation prompt.
#   --dry-run,  Show what would be removed and exit without changing anything.
#     -n
#   --help, -h  Show this help.
#
# WP-CLI: the DB flags run `wp`. Override the binary (e.g. a docker wrapper) with the
# WP_CLI env var, and pass extra args with WP_CLI_ARGS. If `wp` can't reach the DB, the
# DB steps are skipped with a warning; the filesystem reset still completes.
#
# @package CustomTheme

set -euo pipefail

# --- locate the theme root (parent of this script's dir) --------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${THEME_DIR}"

WP_CLI="${WP_CLI:-wp}"
# shellcheck disable=SC2206
WP_CLI_ARGS=(${WP_CLI_ARGS:-})

# --- colours (fall back to plain when not a tty) ----------------------------------------
if [[ -t 1 ]]; then
  BOLD=$'\033[1m'; DIM=$'\033[2m'; RED=$'\033[31m'; GREEN=$'\033[32m'; YELLOW=$'\033[33m'; RESET=$'\033[0m'
else
  BOLD=''; DIM=''; RED=''; GREEN=''; YELLOW=''; RESET=''
fi
say()  { printf '%s\n' "$*"; }
info() { printf '%s\n' "${DIM}$*${RESET}"; }
warn() { printf '%s\n' "${YELLOW}! $*${RESET}"; }
ok()   { printf '%s\n' "${GREEN}✓ $*${RESET}"; }
die()  { printf '%s\n' "${RED}✗ $*${RESET}" >&2; exit 1; }

# --- parse args -------------------------------------------------------------------------
DO_CONTENT=0 DO_MENUS=0 DO_PRESET=0 DO_MEDIA=0 ASSUME_YES=0 DRY_RUN=0
for arg in "$@"; do
  case "$arg" in
    --content) DO_CONTENT=1 ;;
    --menus)   DO_MENUS=1 ;;
    --preset)  DO_PRESET=1 ;;
    --media)   DO_MEDIA=1 ;;
    --all)     DO_CONTENT=1; DO_MENUS=1; DO_PRESET=1; DO_MEDIA=1 ;;
    -y|--yes)  ASSUME_YES=1 ;;
    -n|--dry-run) DRY_RUN=1 ;;
    -h|--help) sed -n '3,33p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) die "Unknown argument: ${arg} (use --help)" ;;
  esac
done

# --- discover targets -------------------------------------------------------------------
# `mbn-ai-` source dirs (sections + components) and their build output.
mapfile -t AI_SRC   < <(find src -maxdepth 2 -type d -name 'mbn-ai-*' 2>/dev/null | sort)
mapfile -t AI_BUILD < <(find build -maxdepth 2 -type d -name 'mbn-ai-*' 2>/dev/null | sort)
COMPONENTS_SRC_DIR="src/components"
COMPONENTS_BUILD_DIR="build/components"
# The live registry of built mbn-ai- blocks/components (the one mutable AI-state file).
REGISTRY="${THEME_DIR}/.claude/skills/ai-blocks/SKILL.md"

# --- show the plan ----------------------------------------------------------------------
say ""
say "${BOLD}Reset mbn-theme for a new project${RESET}"
say "${DIM}Theme: ${THEME_DIR}${RESET}"
say ""
say "${BOLD}Will remove (filesystem):${RESET}"
if [[ ${#AI_SRC[@]} -eq 0 && ${#AI_BUILD[@]} -eq 0 ]]; then
  info "  (no mbn-ai-* blocks/components found)"
else
  for d in "${AI_SRC[@]}";   do say "  - ${d}"; done
  for d in "${AI_BUILD[@]}"; do say "  - ${d}"; done
  [[ -d "${COMPONENTS_SRC_DIR}" ]]   && say "  - ${COMPONENTS_SRC_DIR}/ (if empty after)"
  [[ -d "${COMPONENTS_BUILD_DIR}" ]] && say "  - ${COMPONENTS_BUILD_DIR}/"
fi
say "  - reset components registry → '_none yet_' (${DIM}${REGISTRY#${THEME_DIR}/}${RESET})"
say "  - then: npm run build + composer run lint"
say ""
if [[ ${DO_CONTENT} -eq 1 || ${DO_MENUS} -eq 1 || ${DO_PRESET} -eq 1 || ${DO_MEDIA} -eq 1 ]]; then
  say "${BOLD}Will also (database / WP-CLI):${RESET}"
  [[ ${DO_CONTENT} -eq 1 ]] && say "  - --content: empty built pages, reset header/footer templates, delete seeded testimonials"
  [[ ${DO_MENUS}   -eq 1 ]] && say "  - --menus:   delete AI-created nav menus + clear template menu meta"
  [[ ${DO_PRESET}  -eq 1 ]] && say "  - --preset:  reset mbn_settings to theme defaults"
  [[ ${DO_MEDIA}   -eq 1 ]] && say "  - --media:   LIST AI-uploaded media for manual review (not auto-deleted)"
  say ""
fi
say "${BOLD}Never touched:${RESET} core mbn- blocks (mbn-section/container/columns/column/logo/menu), src/shared/, template-reuse."
say ""

if [[ ${DRY_RUN} -eq 1 ]]; then
  ok "Dry run — nothing changed."
  exit 0
fi

# --- confirm ----------------------------------------------------------------------------
if [[ ${ASSUME_YES} -ne 1 ]]; then
  printf '%s' "${RED}${BOLD}This is destructive and irreversible.${RESET} Type ${BOLD}yes${RESET} to proceed: "
  read -r reply
  [[ "${reply}" == "yes" ]] || die "Aborted — no changes made."
fi
say ""

# --- WP-CLI helper ----------------------------------------------------------------------
WP_OK=-1  # -1 unchecked, 0 usable, 1 unusable
wp_ready() {
  if [[ ${WP_OK} -eq -1 ]]; then
    if command -v "${WP_CLI%% *}" >/dev/null 2>&1 && "${WP_CLI}" "${WP_CLI_ARGS[@]}" option get siteurl >/dev/null 2>&1; then
      WP_OK=0
    else
      WP_OK=1
    fi
  fi
  return ${WP_OK}
}
wp_run() { "${WP_CLI}" "${WP_CLI_ARGS[@]}" "$@"; }

need_wp() {
  if wp_ready; then return 0; fi
  warn "WP-CLI ('${WP_CLI}') can't reach the site — skipping DB step: $1"
  warn "  Set WP_CLI (e.g. a docker wrapper) / WP_CLI_ARGS and re-run with just that flag."
  return 1
}

# --- 1. filesystem reset ----------------------------------------------------------------
say "${BOLD}› Removing mbn-ai-* sources & builds${RESET}"
for d in "${AI_SRC[@]}" "${AI_BUILD[@]}"; do
  [[ -d "$d" ]] && rm -rf "$d" && info "  removed $d"
done
# Remove the components dirs, but only the components tree (all components are mbn-ai-).
[[ -d "${COMPONENTS_BUILD_DIR}" ]] && rm -rf "${COMPONENTS_BUILD_DIR}" && info "  removed ${COMPONENTS_BUILD_DIR}"
if [[ -d "${COMPONENTS_SRC_DIR}" ]]; then
  find "${COMPONENTS_SRC_DIR}" -maxdepth 1 -type d -name 'mbn-ai-*' -exec rm -rf {} + 2>/dev/null || true
  # drop the now-empty components/ dir so the tree matches a fresh checkout
  rmdir "${COMPONENTS_SRC_DIR}" 2>/dev/null && info "  removed empty ${COMPONENTS_SRC_DIR}/" || true
fi
ok "Sources & builds cleaned."

# --- 2. reset the components registry table ---------------------------------------------
if [[ -f "${REGISTRY}" ]]; then
  say "${BOLD}› Resetting components registry${RESET}"
  # Blank every data row (lines starting with '|' that aren't the header/separator)
  # inside the "## Registry" section, and keep the "_none yet_" note. The section ends
  # at the next heading (or EOF).
  awk '
    /^#/ && inreg && $0 !~ /^## Registry/ { inreg=0 }
    /^## Registry/ { inreg=1 }
    {
      if (inreg && $0 ~ /^\|/) {
        if ($0 ~ /Purpose/ || $0 ~ /^\|[- ]*\|/) { print; next }  # keep header + separator
        next                                                       # drop data rows
      }
      print
    }
  ' "${REGISTRY}" > "${REGISTRY}.tmp" && mv "${REGISTRY}.tmp" "${REGISTRY}"
  grep -q '_none yet_' "${REGISTRY}" || warn "Registry '_none yet_' note not found — review ${REGISTRY} manually."
  ok "Registry cleared."
  # Regenerate .cursor/ + AGENTS.md from the edited .claude/ source (source-of-truth rule).
  if [[ -x "${SCRIPT_DIR}/sync-ai-config.sh" ]]; then
    "${SCRIPT_DIR}/sync-ai-config.sh" >/dev/null 2>&1 && info "  synced .cursor/ + AGENTS.md" || warn "sync-ai-config.sh failed — run it manually."
  fi
fi

# --- 3. optional DB resets --------------------------------------------------------------
if [[ ${DO_PRESET} -eq 1 ]] && need_wp "--preset (reset mbn_settings)"; then
  say "${BOLD}› Resetting preset (mbn_settings)${RESET}"
  wp_run eval 'update_option( "mbn_settings", function_exists("mbn_settings_defaults") ? mbn_settings_defaults() : array() );' \
    && ok "Preset reset to defaults." || warn "Preset reset failed."
fi

if [[ ${DO_MENUS} -eq 1 ]] && need_wp "--menus (delete AI nav menus)"; then
  say "${BOLD}› Deleting AI-created nav menus${RESET}"
  for menu in "Header Menu" "Footer Menu" "Footer Services" "Social Menu" "Footer Legal"; do
    if wp_run menu list --field=name 2>/dev/null | grep -qxF "${menu}"; then
      wp_run menu delete "${menu}" >/dev/null 2>&1 && info "  deleted menu: ${menu}" || warn "  could not delete: ${menu}"
    fi
  done
  # Clear template menu-slot meta so header/footer templates stop referencing deleted menus.
  wp_run eval '$q=get_posts(array("post_type"=>"mbn_block_template","numberposts"=>-1,"fields"=>"ids"));
    foreach($q as $id){ delete_post_meta($id,"_mbn_template_menus"); }' >/dev/null 2>&1 \
    && info "  cleared _mbn_template_menus on templates" || true
  ok "Menus reset."
fi

if [[ ${DO_CONTENT} -eq 1 ]] && need_wp "--content (empty pages/templates/testimonials)"; then
  say "${BOLD}› Resetting built content${RESET}"
  # Delete seeded testimonials.
  wp_run eval '$q=get_posts(array("post_type"=>"mbn_testimonial","numberposts"=>-1,"fields"=>"ids","post_status"=>"any"));
    foreach($q as $id){ wp_delete_post($id,true); } echo count($q);' >/dev/null 2>&1 \
    && info "  deleted seeded testimonials" || true
  warn "Page post_content + header/footer templates: review which post IDs to blank, then run e.g.:"
  warn "  ${WP_CLI} post update <ID> --post_content=''"
  warn "  (left manual so a real page is never wiped by accident)"
  ok "Content step done (testimonials cleared)."
fi

if [[ ${DO_MEDIA} -eq 1 ]]; then
  say "${BOLD}› Media (manual review — not auto-deleted)${RESET}"
  if need_wp "--media (list AI-uploaded attachments)"; then
    warn "Media removal is dedup-sensitive and is NOT automated. Candidate attachments:"
    wp_run post list --post_type=attachment --fields=ID,post_title,post_mime_type --format=table 2>/dev/null || true
    warn "Delete the AI-uploaded ones via the WP admin or: ${WP_CLI} post delete <ID> --force"
  fi
fi

# --- 4. rebuild + lint ------------------------------------------------------------------
say "${BOLD}› Rebuilding${RESET}"
if command -v npm >/dev/null 2>&1; then
  npm run build && ok "npm run build passed." || die "npm run build failed."
else
  warn "npm not found — run 'npm run build' manually."
fi

say "${BOLD}› Linting${RESET}"
if command -v composer >/dev/null 2>&1; then
  composer run lint && ok "composer run lint passed." || warn "composer run lint reported issues — review above."
else
  warn "composer not found — run 'composer run lint' manually."
fi

# --- done -------------------------------------------------------------------------------
say ""
ok "Reset complete."
say "${DIM}src/ now holds only core mbn- blocks + shared/ (+ template-reuse). Ready for a new /build-design or /build-components run.${RESET}"
