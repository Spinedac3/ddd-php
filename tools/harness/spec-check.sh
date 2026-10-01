#!/bin/bash
#
# Gate de forma de la spec: lo que la plantilla exige, verificado con máquina.
#
# Por qué existe: las reglas del artefacto las cuidaba el mismo agente que redacta — el que
# la propia plantilla declara como modo de fallo por defecto. Cada check de acá es un defecto
# ya pagado: una spec que congeló con pendientes vivos y ambas zonas fallaron; una que nombró
# una excepción base pelada y se implementó así en cuatro lugares; una publicación que perdió
# secciones sin que nadie lo viera.
#
# Uso:
#   bash tools/harness/spec-check.sh <spec.md>              # forma de la spec
#   bash tools/harness/spec-check.sh <spec.md> <otra.md>    # + compara secciones ## (SP6)
#
# exit 0 = limpia · exit 1 = violaciones · exit 2 = no pudo correr
set -uo pipefail

SPEC="${1:-}"
OTRA="${2:-}"
[ -f "$SPEC" ] && [ -s "$SPEC" ] || { echo "spec-check: NO PUDO CORRER — no existe o esta vacia: $SPEC"; exit 2; }

KIT="$(cd "$(dirname "$0")" && pwd)"
V=0
falla() { V=$((V+1)); echo "SP$1  $2"; }

# SP1 — eje 0 completo: cada pieza de la cadena con su fila («no se toca» también). Las piezas
# se declaran una por línea en tools/harness/cadena.txt; sin archivo, la cadena es este repo.
if [ -f "$KIT/cadena.txt" ]; then
    PIEZAS=$(grep -vE '^\s*(#|$)' "$KIT/cadena.txt" | tr -d '\r')
else
    PIEZAS="library"
fi
while IFS= read -r kw; do
    [ -z "$kw" ] && continue
    grep -qiE "^\|.*\b$kw\b" "$SPEC" || falla 1 "eje 0: falta la fila de '$kw' — una fila ausente invalida la spec"
done <<< "$PIEZAS"

# SP2 — nada congela con pendientes.
PEND='\[(confirmar|pendiente|verificar|por definir|definir despues|TBD|TODO)'
while IFS= read -r l; do
    [ -n "$l" ] && falla 2 "pendiente vivo: $l"
done < <(grep -nEi "$PEND" "$SPEC")

# SP3 — excepción base pelada: las tres bases se extienden, no se instancian. Una mención sin
# 'extends' ni subclase nombrada es la spec pidiendo tirar la base.
while IFS= read -r l; do
    [ -n "$l" ] && falla 3 "base pelada (se extiende, no se nombra sola): $l"
done < <(grep -nE '(^|[^A-Za-z\{])(ConflictException|NotFoundException|GenericException)\b' "$SPEC" \
    | grep -vE 'extends|subclas|\{X\}|\{Entity\}')

# SP4 — seam decisorio declarado, con contenido.
grep -qiE '^>? ?\**Seam decisorio\**[:：].{10,}' "$SPEC" \
    || falla 4 "eje 6: falta el seam decisorio (la sonda + el valor que solo el referente correcto produce)"

# SP5 — DURADERA consistente: los D# de la línea resumen = las filas marcadas.
RES=$(grep -m1 -iE '^\**Duraderas[:：]' "$SPEC" || true)
FILAS=$(grep -cE '^- +D[0-9]+ +DURADERA\b' "$SPEC")
if [ -z "$RES" ]; then
    falla 5 "mapa de decisiones: falta la linea resumen '**Duraderas: ...**' (aunque sea vacia: 'Duraderas: —')"
else
    NRES=$(printf '%s' "$RES" | grep -oE 'D[0-9]+' | wc -l)
    [ "$NRES" -ne "$FILAS" ] && falla 5 "duraderas: la linea resumen lista $NRES y hay $FILAS filas marcadas DURADERA"
fi

# SP8 — la sección de retroalimentación al método existe (aunque diga 'sin hallazgos'). Es la
# que se recorre transversalmente en todos los issues para capturar las mejoras del flujo.
grep -qiE '^#+ +Retroalimentaci.n al m.todo' "$SPEC" \
    || falla 8 "falta la seccion '## Retroalimentación al método (SDD)' — fija, recorrible entre issues; si no hubo hallazgos, lo dice"

# SP7 — nombres: rutas, clases, métodos y tests que la spec PROPONE, en inglés y con la
# gramática de las piezas. Es la MISMA regla que layers.sh corre sobre el código
# (tools/architecture/naming.sh): lo que acá no pasa, el PR lo rechazaría después.
NAMING="$KIT/../architecture/naming.sh"
SPEC_ABS="$(cd "$(dirname "$SPEC")" && pwd)/$(basename "$SPEC")"
if [ -f "$NAMING" ]; then
    while IFS= read -r l; do
        [ -n "$l" ] && { echo "SP7  $l"; V=$((V+1)); }
    done < <(bash "$NAMING" --spec "$SPEC_ABS" 2>/dev/null | grep -E '^N[0-9]')
else
    echo "spec-check: NO PUDO CORRER SP7 — falta tools/architecture/naming.sh"
fi

# SP6 — dos copias, mismas secciones: una sección que no viaja convierte propuestas en hechos.
if [ -n "$OTRA" ]; then
    [ -f "$OTRA" ] || { echo "spec-check: NO PUDO CORRER — no existe: $OTRA"; exit 2; }
    D=$(diff <(grep -E '^## ' "$SPEC") <(grep -E '^## ' "$OTRA") || true)
    if [ -n "$D" ]; then
        echo "SP6  las secciones ## difieren entre $SPEC y $OTRA:"
        printf '%s\n' "$D" | sed 's/^/      /'
        V=$((V+1))
    fi
fi

echo "spec-check: $V violacion(es) en $SPEC"
[ "$V" -gt 0 ] && exit 1
exit 0
