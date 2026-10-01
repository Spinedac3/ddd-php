#!/bin/bash
#
# Gate de NOMBRES: idioma y gramática de las piezas, sobre nombres DECLARADOS.
#
# Una sola regla con dos consumidores: `spec-check.sh` (SP7) la corre sobre los nombres que
# la spec PROPONE, y `layers.sh` (N) sobre los archivos NUEVOS del cambio. Así la spec respeta
# exactamente lo mismo que el código, y lo respeta ANTES de que exista el código.
#
# Por qué existe: una spec nombró clases, métodos y tests en español, el rojo los copió, el
# implementador los siguió, y ningún gate lo vio — la regla «identificadores en inglés» estaba
# escrita y nadie la contaba.
#
# Dos defectos que pagó la primera versión, ambos cazados por el control positivo:
#   · `... | procesar` corría el conteo en un subshell: imprimía las violaciones y salía 0.
#   · un proceso por palabra no terminaba en un árbol grande. El motor es UNA pasada de awk.
#
# Uso:
#   bash tools/architecture/naming.sh --spec <spec.md>       # rutas, clases, métodos, tests del texto
#   bash tools/architecture/naming.sh --files <a.php> ...    # ruta + clase + métodos públicos + $variables
#   printf 'Nombre\n' | bash tools/architecture/naming.sh --names
#
# Salida: una línea `N<k>  <nombre>  <motivo>` por violación y un resumen. exit 1 si hay alguna.
#
# Reglas:
#   N1 — idioma: sin caracteres no-ASCII y sin palabras en español dentro del identificador
#        (clases, métodos, tests, $variables y parámetros). Las DECLARACIONES de propiedades de
#        Entities y Persistencies quedan fuera: espejan columnas de la base, no se eligen.
#        La lista de raíces es finita y se amplía cuando algo se escapa: no es un diccionario,
#        es el registro de lo que ya pasó.
#   N2 — un Service NUEVO se llama por la cosa que posee: `{X}Service` exige `{X}` como
#        Entity, ValueObject o `{X}Aggregate` en el árbol o declarado en la misma spec.
#        En --spec, una ruta que ya existe en el árbol es un servicio EXISTENTE y no aplica.
#   N3 — tests camelCase: `test[A-Z]…`, nunca `test_snake`.
#   N4 — un nombre que termina en Aggregate arranca en mayúscula y no lleva snake_case.
#
# Techos conocidos: tablas y columnas NUEVAS no se chequean, y una sigla pegada a la palabra
# (`QRNombre` → «qrnombre») no matchea la raíz. Ampliar cuando alguno se pague.
set -uo pipefail
cd "$(dirname "$0")/../.." || exit 2
export LC_ALL=C

MODO="${1:-}"; shift || true

# Raíces en español que aparecen en identificadores. Palabra completa del CamelCase, en
# minúscula. Las propias del proyecto: una por línea en tools/architecture/raices.txt.
RAICES='nombre nombres codigo codigos fecha fechas estado estados descripcion usuario usuarios
cantidad resultado resultados aviso avisos caso casos metodo metodos registrar evaluar comparar
entrada salida devolucion sin tras del por para esta usa antes despues cuenta hora horas
buscar guardar borrar crear listar pedido pedidos cliente clientes envio envios'
[ -f tools/architecture/raices.txt ] && RAICES="$RAICES $(grep -vE '^\s*(#|$)' tools/architecture/raices.txt | tr -d '\r' | tr '\n' ' ')"

extraer_spec() { # $1 = spec.md
    grep -oE '(src|tests)/[A-Za-z0-9_/]+\.php' "$1"
    grep -oE '\b[A-Z][A-Za-z0-9]*(Service|Repository|Aggregate|Factory|Exception|Model)\b' "$1"
    grep -oE '::test[A-Za-z0-9_]+' "$1" | sed 's/^:://'
    grep -oE '(^|[^A-Za-z0-9_])[a-z][A-Za-z0-9_]*\(\)' "$1" | sed -E 's/^[^a-z]//; s/\(\)$//'
}

extraer_files() { # $@ = archivos php; un grep por regla para TODOS los archivos (velocidad)
    printf '%s\n' "$@"
    grep -hoE '^(abstract |final )?(class|interface|trait) [A-Za-z0-9_]+' "$@" 2>/dev/null | awk '{print $NF}'
    grep -hoE 'public function [a-zA-Z0-9_]+' "$@" 2>/dev/null | awk '{print $3}' | grep -vE '^__' || true
    for f in "$@"; do
        case "$f" in
            src/Entities/*|src/Persistencies/*|src/Traits/Entities/*)
                grep -vE '^\s*(public|protected|private)\s+(static\s+)?(\??[A-Za-z0-9_|\\]+\s+)?\$' "$f" 2>/dev/null ;;
            *) cat "$f" 2>/dev/null ;;
        esac
    done | grep -oE '\$[a-zA-Z_][A-Za-z0-9_]+' | grep -vE '^\$this$' || true
}

case "$MODO" in
    --spec)
        SPEC="${1:-}"
        [ -f "$SPEC" ] || { echo "naming: NO PUDO CORRER — no existe la spec: $SPEC"; exit 2; }
        DECLARADOS=$(grep -oE '(src|tests)/[A-Za-z0-9_/]+\.php' "$SPEC" | sort -u)
        NOMBRES=$(extraer_spec "$SPEC" | sed '/^$/d' | sort -u)
        ;;
    --files)
        [ "$#" -gt 0 ] || { echo "naming: 0 nombre(s) · 0 violacion(es)"; exit 0; }
        DECLARADOS=""
        NOMBRES=$(extraer_files "$@" | sed '/^$/d' | sort -u)
        ;;
    --names)
        DECLARADOS=""
        NOMBRES=$(sed '/^$/d' | sort -u)
        ;;
    *)
        echo "uso: naming.sh --spec <spec.md> | --files <php...> | --names (stdin)"; exit 2 ;;
esac

[ -z "$NOMBRES" ] && { echo "naming: 0 nombre(s) · 0 violacion(es)"; exit 0; }
TOTAL=$(printf '%s\n' "$NOMBRES" | wc -l)

# Motor: UNA pasada de awk. Divide CamelCase/snake_case a palabras y aplica N1/N3/N4.
OUT=$(printf '%s\n' "$NOMBRES" | awk -v raices="$RAICES" '
BEGIN {
    n = split(raices, a, /[ \n]+/); for (i = 1; i <= n; i++) if (a[i] != "") R[a[i]] = 1
}
function partir(s,    i, c, prev, out) {
    out = ""; prev = ""
    for (i = 1; i <= length(s); i++) {
        c = substr(s, i, 1)
        if (c !~ /[A-Za-z0-9]/) { out = out " "; prev = c; continue }
        if (c ~ /[A-Z]/ && prev ~ /[a-z0-9]/) out = out " "
        out = out c; prev = c
    }
    return tolower(out)
}
{
    name = $0
    if (name ~ /[^ -~]/) {
        printf "N1  %s  identificador con caracteres no-ASCII: los identificadores van en ingles\n", name
        next
    }
    if (name ~ /(^|::)test_/)
        printf "N3  %s  los tests son camelCase (testFindsByCode), nunca test_snake_case\n", name
    base = name; sub(/\.php$/, "", base); sub(/.*\//, "", base)
    # N4 es gramatica de CLASES: un metodo (arranca en minuscula) que termina en
    # "...WithAggregate" no es un aggregate.
    if (base ~ /Aggregate$/ && base ~ /^[A-Z]/ && base !~ /^[A-Z][A-Za-z0-9]*Aggregate$/)
        printf "N4  %s  un aggregate es CamelCase segun la gramatica {X}[With{Y}][Create|Update|Listing]Aggregate\n", name
    k = split(partir(name), w, / +/)
    for (i = 1; i <= k; i++) {
        if (w[i] == "") continue
        if (w[i] in R) {
            printf "N1  %s  palabra en espanol: los identificadores van en ingles (CLAUDE.md, Style)\n", name
            break
        }
    }
}')

# N2 en bash: pocos candidatos y necesita mirar el árbol / lo declarado en la spec.
N2=""
for ruta in $(printf '%s\n' "$NOMBRES" | grep -E '^src/Services/.*Service\.php$' || true); do
    [ "$MODO" = "--spec" ] && [ -f "$ruta" ] && continue
    x=$(basename "$ruta" Service.php)
    if find src/Entities src/ValueObjects -name "$x.php" 2>/dev/null | grep -q . \
        || find src/Aggregates -name "${x}Aggregate.php" 2>/dev/null | grep -q . \
        || printf '%s\n' "$DECLARADOS" | grep -qE "/(Entities|ValueObjects)/.*/$x\.php$|/Aggregates/.*/${x}Aggregate\.php$"; then
        continue
    fi
    N2=$(printf '%s\n%s' "$N2" "N2  $ruta  un Service nuevo se llama por la cosa que posee: no hay Entity, ValueObject ni ${x}Aggregate llamado «$x» (ni en el arbol ni en la spec)")
done

SALIDA=$(printf '%s\n%s\n' "$OUT" "$N2" | sed '/^$/d')
V=$(printf '%s\n' "$SALIDA" | grep -cE '^N[0-9]' || true)
[ -n "$SALIDA" ] && printf '%s\n' "$SALIDA"
echo "naming: $TOTAL nombre(s) · $V violacion(es)"
[ "${V:-0}" -gt 0 ] && exit 1
exit 0
