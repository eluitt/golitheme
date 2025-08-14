#!/usr/bin/env bash
set -euo pipefail

THEME_DIR="themes/golitheme"
BASE_BRANCH="golitheme"   # معادل develop

# پیدا کردن شماره گام بعدی بر اساس تگ‌های step-XX-*
next_step_number() {
  local last n
  last=$(git tag --list 'step-[0-9][0-9]-*' --sort=creatordate | tail -n1 | sed -E 's/^step-([0-9][0-9])-.*/\1/' || true)
  if [[ -z "${last:-}" ]]; then n=1; else n=$((10#$last + 1)); fi
  printf "%02d" "${n}"
}

case "${1:-}" in
  "feature:start")
    name="${2:?Usage: steps.sh feature:start <slug>}"
    git fetch origin --prune
    git switch "${BASE_BRANCH}" || git switch -c "${BASE_BRANCH}"
    git pull --ff-only || true
    git switch -c "feature/${name}"
    git push -u origin HEAD
    echo "✅ feature/${name} created & pushed"
    ;;

  "feature:commit")
    msg="${2:?Usage: steps.sh feature:commit \"<commit message>\"}"
    git add "${THEME_DIR}" -A
    if git diff --cached --quiet; then
      echo "ℹ️ No staged changes under ${THEME_DIR}"
    else
      git commit -m "${msg}"
      git push
      echo "✅ committed & pushed"
    fi
    ;;

  "step:tag")
    slug="${2:?Usage: steps.sh step:tag <slug>}"
    git fetch --tags origin || true
    step_no=$(next_step_number)
    stamp=$(date +%Y%m%d-%H%M)
    tag="step-${step_no}-${slug}-${stamp}"
    git tag -a "${tag}" -m "Step ${step_no}: ${slug} (${stamp})"
    git push origin "${tag}"
    echo "🏷  created tag: ${tag}"
    ;;

  *)
    cat <<EOF
Usage:
  bash scripts/steps.sh feature:start <slug>
  bash scripts/steps.sh feature:commit "<commit message>"
  bash scripts/steps.sh step:tag <slug>
Notes:
  - No merges/releases here. Only feature workflow + step tagging.
  - Tags look like: step-01-header-20250814-1420
EOF
    exit 1
    ;;
esac
