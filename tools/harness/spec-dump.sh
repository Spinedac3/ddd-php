#!/bin/bash
#
# Baja el dump del issue — el texto que se congela y se commitea (docs/specs/<n>/issue.md).
#
# Por qué existe: el sha del recibo certifica ESTE texto. Cualquier dev lo baja a mano, y CI
# también: sin poder re-bajarlo, la frescura dependería de que hubiera un agente en la sesión.
#
# Uso:
#   bash tools/harness/spec-dump.sh <owner/repo> <numero> [salida]
#   bash tools/harness/spec-dump.sh Spinedac3/ddd-php 12            # -> docs/specs/12/issue.md
#
# El token es opcional en repos públicos y obligatorio en privados: variable GITHUB_TOKEN, o
# un archivo .env con GITHUB_TOKEN=... señalado por GITHUB_ENV_FILE. Solo lectura.
#
# exit 0 = dump escrito · exit 2 = no pudo (token, red, issue inexistente)
set -uo pipefail

REPO="${1:-}"; NUM="${2:-}"
[ -n "$REPO" ] && [ -n "$NUM" ] || { echo "uso: spec-dump.sh <owner/repo> <numero> [salida]"; exit 2; }
SALIDA="${3:-docs/specs/$NUM/issue.md}"
URL="${GITHUB_API_URL:-https://api.github.com}"

if [ -z "${GITHUB_TOKEN:-}" ] && [ -n "${GITHUB_ENV_FILE:-}" ] && [ -f "$GITHUB_ENV_FILE" ]; then
    GITHUB_TOKEN=$(grep -m1 '^GITHUB_TOKEN=' "$GITHUB_ENV_FILE" | cut -d= -f2-)
fi

AUTH=()
[ -n "${GITHUB_TOKEN:-}" ] && AUTH=(-H "Authorization: Bearer $GITHUB_TOKEN")

JSON=$(curl -sf --max-time 30 -H "Accept: application/vnd.github+json" "${AUTH[@]}" "$URL/repos/$REPO/issues/$NUM") \
    || { echo "spec-dump: NO PUDO CORRER — la API no respondio ($URL, $REPO#$NUM); en un repo privado falta GITHUB_TOKEN"; exit 2; }

mkdir -p "$(dirname "$SALIDA")"
# php porque jq no viene en todas las máquinas y php sí (es un repo php).
printf '%s' "$JSON" | php -r '
    $i = json_decode(stream_get_contents(STDIN), true);
    if (!isset($i["number"])) { fwrite(STDERR, "spec-dump: respuesta sin issue\n"); exit(2); }
    printf("---\n");
    printf("proyecto: %s\n", $argv[1]);
    printf("iid: %d\n", $i["number"]);
    printf("titulo: %s\n", str_replace("\n", " ", $i["title"]));
    printf("estado: %s\n", $i["state"] === "closed" ? "cerrado" : "abierto");
    printf("actualizado_en_github: %s\n", $i["updated_at"]);
    printf("fetched_at: %s\n", date("Y-m-d\TH:i:s"));
    printf("---\n\n%s\n", str_replace("\r\n", "\n", (string) $i["body"]));
' "$REPO" > "$SALIDA" || exit 2

echo "spec-dump: $REPO#$NUM -> $SALIDA ($(wc -l < "$SALIDA") lineas, estado $(grep -m1 '^estado:' "$SALIDA" | cut -d' ' -f2))"
