#!/usr/bin/env bash
set -euo pipefail

# برنچ توسعه (قابل‌تغییر با ENV: GN_DEV_BRANCH)
DEV_BRANCH="${GN_DEV_BRANCH:-golitheme}"

# --- Helpers ---
is_clean_worktree() { git diff --quiet && git diff --cached --quiet; }

maybe_ff_pull() {
  # فقط اگر GN_AUTOPULL=1 بود و شرایط امن بود، Fast-Forward می‌کنیم
  if [ "${GN_AUTOPULL:-0}" = "1" ]; then
    if is_clean_worktree; then
      git fetch --all --prune || true
      if git rev-parse --verify "origin/${DEV_BRANCH}" >/dev/null 2>&1 \
         && git merge-base --is-ancestor HEAD "origin/${DEV_BRANCH}"; then
        git merge --ff-only "origin/${DEV_BRANCH}" || echo "skip: unable to ff-only"
      else
        echo "skip: local diverged or no remote; not fast-forwarding"
      fi
    else
      echo "skip: working tree dirty; not pulling"
    fi
  fi
}

# --- Commands ---
feature_start() {
  local name="${1:-00-tooling}"
  git fetch --all --tags || true
  git checkout "${DEV_BRANCH}" 2>/dev/null || git checkout -b "${DEV_BRANCH}"
  maybe_ff_pull
  # اگر feature وجود داشت، همون رو checkout کن؛ اگر نبود، بساز
  git checkout -b "feature/${name}" 2>/dev/null || git checkout "feature/${name}"
  echo "Started feature/${name} from ${DEV_BRANCH}"
}

feature_commit() {
  # پیام را از GN_COMMIT_MSG یا از باقی آرگومان‌ها بگیر (با فاصله/پرانتز مشکلی ندارد)
  local msg="${GN_COMMIT_MSG:-${*:-chore: commit}}"
  git add -A
  git commit -m "$msg" || echo "nothing to commit"
  git --no-pager log -1 --oneline || true
}

feature_push() {
  local cur="$(git rev-parse --abbrev-ref HEAD)"
  git push -u origin "${cur}"
  git push origin "${DEV_BRANCH}" || true
  git push --tags || true
  echo "Pushed ${cur}, ${DEV_BRANCH}, and tags."
}

step_tag() {
  local slug="${1:-00-tooling}"
  local tag="step-${slug}"
  local cur="$(git rev-parse --abbrev-ref HEAD)"
  git tag -a "${tag}" -m "${tag}" -f
  git push origin "HEAD:refs/heads/${cur}"
  git push origin "HEAD:refs/heads/${DEV_BRANCH}" || true
  git push origin --tags --force
  echo "Tagged ${tag} and pushed branches."
}

case "${1-}" in
  "feature:start")
    shift
    feature_start "${1:-00-tooling}"
    ;;
  "feature:commit")
    shift
    [ "${1-}" = "--" ] && shift
    feature_commit "$@"
    ;;
  "feature:push")
    feature_push
    ;;
  "step:tag")
    shift
    step_tag "${1:-00-tooling}"
    ;;
  *)
    echo "Usage: $0 {feature:start <name>|feature:commit <msg...>|feature:push|step:tag <slug>}"
    echo "ENV: GN_DEV_BRANCH (default: golitheme), GN_AUTOPULL=1 (optional), GN_COMMIT_MSG"
    exit 1
    ;;
esac
