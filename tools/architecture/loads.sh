#!/bin/bash
#
# Gate de carga: sintaxis + declaración real de cada clase tocada.
#
# Por qué existe: los chequeos de estilo (phpcs) y los de forma no ven que una clase no se
# pueda declarar. El caso típico: un repositorio que declara `implements Contract` y no
# define un método del contrato — PSR-12 limpio, y fatal al cargar.
#
# Uso:
#   bash tools/architecture/loads.sh            # solo lo cambiado contra la rama base
#   bash tools/architecture/loads.sh --all      # todo src/
#   bash tools/architecture/loads.sh a.php b.php
#
# exit 0 = todo carga · exit 1 = alguna clase no carga o no compila · exit 2 = no pudo correr

set -uo pipefail
cd "$(dirname "$0")/../.." || exit 2

[ -f vendor/autoload.php ] || { echo "loads: NO PUDO CORRER — falta vendor/autoload.php (composer install)"; exit 2; }

if [ "${1:-}" = "--all" ]; then
    FILES=$(find src -name '*.php')
elif [ "$#" -gt 0 ]; then
    FILES="$*"
else
    FILES=$(bash tools/architecture/cambiados.sh 'src/*.php')
fi

if [ -z "$FILES" ]; then
    echo "loads: no hay archivos de src/ que revisar."
    exit 0
fi

# Si el cambio toca un contrato, hay que revisar también a quien lo implementa AUNQUE no lo
# toque: agregar un método a la interfaz deja al implementador con un método abstracto sin
# definir y fatala al cargarlo. Ese archivo no está en el diff.
IMPLEMENTADORES=""
for c in $FILES; do
    case "$c" in src/Contracts/*) ;; *) continue ;; esac
    nombre=$(grep -m1 -E '^interface ' "$c" | sed -E 's/^interface ([A-Za-z0-9_]+).*/\1/')
    [ -z "$nombre" ] && continue
    for impl in $(grep -rlE "implements .*\b${nombre}\b|as Contract" src/Repositories/ 2>/dev/null); do
        grep -qF "$nombre" "$impl" && IMPLEMENTADORES="$IMPLEMENTADORES $impl"
    done
done

if [ -n "$IMPLEMENTADORES" ]; then
    FILES=$(printf '%s\n%s\n' "$FILES" "$(printf '%s\n' $IMPLEMENTADORES)" | sed '/^$/d' | sort -u)
fi

TOTAL=0
SYNTAX_BAD=0
LOAD_BAD=0

for f in $FILES; do
    [ -f "$f" ] || continue
    TOTAL=$((TOTAL + 1))

    if ! php -l "$f" > /dev/null 2>&1; then
        echo "SINTAXIS  $f"
        php -l "$f" 2>&1 | head -2 | sed 's/^/          /'
        SYNTAX_BAD=$((SYNTAX_BAD + 1))
        continue
    fi

    ns=$(grep -m1 '^namespace ' "$f" | sed 's/^namespace //; s/;.*//' | tr -d '\r')
    cn=$(grep -m1 -E '^(final |abstract )?(class|interface|trait|enum) ' "$f" \
        | sed -E 's/^(final |abstract )?(class|interface|trait|enum) ([A-Za-z0-9_]+).*/\3/' | tr -d '\r')

    if [ -z "$ns" ] || [ -z "$cn" ]; then
        continue
    fi

    if ! php tools/architecture/declares.php "$ns\\$cn" > /dev/null 2>&1; then
        echo "NO CARGA  $ns\\$cn"
        php tools/architecture/declares.php "$ns\\$cn" 2>&1 \
            | grep -iE 'fatal|error' | head -1 | sed 's/^/          /'
        LOAD_BAD=$((LOAD_BAD + 1))
    fi
done

echo "loads: $TOTAL archivos · $SYNTAX_BAD con error de sintaxis · $LOAD_BAD que no cargan"

if [ "$SYNTAX_BAD" -gt 0 ] || [ "$LOAD_BAD" -gt 0 ]; then
    exit 1
fi

exit 0
