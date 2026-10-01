#!/bin/bash
# Lista los archivos .php tocados contra la rama base, incluidos los nuevos sin `git add`
# (no aparecen en ningún diff: una clase recién creada pasaba en verde sin mirarse).
#
# Uso (lo llaman los gates; no se corre a mano):
#   bash tools/architecture/cambiados.sh <pathspec>...        # tocados (ACMR) + sin trackear
#   SOLO_NUEVOS=1 bash tools/architecture/cambiados.sh ...    # solo agregados + sin trackear
#
# La rama base: BASE_BRANCH, o GITHUB_BASE_REF en un pull request de GitHub Actions, o `main`.
BASE_BRANCH="${BASE_BRANCH:-${GITHUB_BASE_REF:-main}}"
FILTRO="ACMR"; [ -n "${SOLO_NUEVOS:-}" ] && FILTRO="A"

BASE="origin/$BASE_BRANCH"
git rev-parse -q --verify "$BASE" > /dev/null 2>&1 || BASE="$BASE_BRANCH"

F=$(git diff --name-only --diff-filter=$FILTRO "$BASE...HEAD" -- "$@" 2>/dev/null)
F=$(printf '%s\n%s\n' "$F" "$(git diff --name-only --diff-filter=$FILTRO HEAD -- "$@" 2>/dev/null)")
printf '%s\n%s\n' "$F" "$(git ls-files --others --exclude-standard -- "$@")" | sed '/^$/d' | sort -u
