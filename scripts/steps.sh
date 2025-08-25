#!/usr/bin/env bash
set -euo pipefail

# احترام به ورودی از tasks.json
DEV_BRANCH="${GN_DEV_BRANCH:-golitheme}"

feature_start() {
  local name="${1:-00-tooling}"
  git fetch --all --tags || true
  git checkout "${DEV_BRANCH}" 2>/dev/null || git checkout -b "${DEV_BRANCH}"
  git pull --ff-only || true
  git checkout -b "feature/${name}" || git checkout "feature/${name}"
  echo "Started feature/${name} from ${DEV_BRANCH}"
}

feature_commit() {
  local msg="${*:-chore: commit}"
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
  git tag -f "${tag}"
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
    # اگر بعد از subcommand یک -- گذاشتیم، بخورَش
    [ "${1-}" = "--" ] && shift
    feature_commit "$@"   # تمام بقیهٔ آرگومان‌ها = پیام
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
    exit 1
    ;;
esac
