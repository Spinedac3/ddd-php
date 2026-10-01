#!/bin/bash
#
# Gate de estilo: phpcs PSR-12 sobre lo tocado — el mismo chequeo que corre el pipeline.
#
# Por qué existe: un chequeo que solo corre en CI es un rojo diferido. Una pieza se declaraba
# lista con todos los gates locales en verde y el pipeline salía rojo por indentación.
#
# EOL: se excluye Generic.Files.LineEndings a propósito. En checkouts de Windows los archivos
# llevan CRLF y castigarlos acá haría rojo todo archivo por una causa que el PR no va a ver.
#
# Uso:
#   bash tools/architecture/phpcs.sh            # solo lo cambiado contra la rama base
#   bash tools/architecture/phpcs.sh --all      # todo src/ y tests/
#   bash tools/architecture/phpcs.sh a.php b.php
#
# exit 0 = limpio · exit 1 = violaciones · exit 2 = no hay binario phpcs

set -uo pipefail
cd "$(dirname "$0")/../.." || exit 2

# Sin binario el gate FALLA ruidoso: un gate que no puede correr y sale 0 es un gate que miente.
if [ -f vendor/bin/phpcs ]; then
    PHPCS="php vendor/bin/phpcs"
elif command -v phpcs > /dev/null 2>&1; then
    PHPCS="phpcs"
else
    echo "phpcs: sin binario (ni vendor/bin ni PATH) — composer require --dev squizlabs/php_codesniffer"
    exit 2
fi

PATHSPECS=("src/*.php" "tests/*.php")

if [ "${1:-}" = "--all" ]; then
    FILES=$(for d in src tests; do [ -d "$d" ] && find "$d" -name '*.php'; done)
elif [ "$#" -gt 0 ]; then
    FILES="$*"
else
    FILES=$(bash tools/architecture/cambiados.sh "${PATHSPECS[@]}")
fi

FILES=$(printf '%s\n' $FILES | while read -r f; do [ -f "$f" ] && echo "$f"; done)

if [ -z "$FILES" ]; then
    echo "phpcs: no hay archivos que revisar."
    exit 0
fi

TOTAL=$(printf '%s\n' "$FILES" | wc -l | tr -d ' ')

# shellcheck disable=SC2086
OUT=$($PHPCS --standard=PSR12 --exclude=Generic.Files.LineEndings $FILES 2>&1)
E=$?
# Segunda pasada: una sola línea en blanco entre métodos (phpcs-whitespace.xml). PSR-12 no
# lo mira: varias líneas en blanco entre dos métodos pasan en verde.
# shellcheck disable=SC2086
OUT2=$($PHPCS --standard=tools/architecture/phpcs-whitespace.xml $FILES 2>&1)
[ $? -ne 0 ] && E=1
OUT=$(printf '%s\n%s\n' "$OUT" "$OUT2" | sed '/^$/d')
BAD=$(printf '%s\n' "$OUT" | grep '^FILE: ' | sort -u | wc -l | tr -d ' ')

if [ "$E" -ne 0 ]; then
    printf '%s\n' "$OUT"
fi

echo "phpcs: $TOTAL archivos · $BAD con violaciones"

[ "$E" -ne 0 ] && exit 1
exit 0
