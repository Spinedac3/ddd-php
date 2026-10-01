#!/bin/bash
#
# Gate de dirección: las reglas de capa que ni phpcs ni el gate de carga ven.
#
# Por qué existe: phpcs mira estilo y loads.sh mira que la clase se declare; ninguno de los
# dos mira quién importa a quién. Estas reglas casi nunca se violan por descuido, pero eso es
# una tasa, no una garantía. Y una regla escrita en el prompt de un agente se cita y se viola
# igual — la imitación del archivo vecino le gana a la prohibición; un chequeo determinista no.
#
# Uso:
#   bash tools/architecture/layers.sh            # solo lo cambiado contra la rama base
#   bash tools/architecture/layers.sh --all      # todo src/
#   bash tools/architecture/layers.sh a.php b.php
#
# exit 0 = ninguna violación · exit 1 = alguna regla rota · exit 2 = no pudo correr
#
# Reglas (cada una es un defecto que ya se pagó alguna vez):
#   A1  un repositorio no importa un Factory          B1  un Aggregate no importa Repositories ni Services
#   A2  un Aggregate no importa un Factory            B2  un repositorio solo conoce MainConfigurationService
#   A3  una Entity no importa Repositories/Services   B3  la transacción vive en el Service
#   A4  un ValueObject no importa Repos/Services      B4  un repositorio concreto implementa su contrato
#   A5  sin declare(strict_types=1)                   B5  un repositorio ORM no consulta por conexión directa
#   A6  el envoltorio del listado ya existe           B6  la cadena de consulta va en vertical
#   A7  aridad del contrato = identidad de la entity
#   A8/A9/A10/A12  forma de un Service nuevo (service-rules.php)
#   A11 sin referencias a issues (#NN) en comentarios
#   A13 sin evidencia (conteos, fechas) en comentarios
#   T2  una Entity nueva declara $required y $keyFields
#   F1  un método de búsqueda que puede devolver null se llama find, no get
#   N   nombres en inglés y con la gramática de las piezas (naming.sh)

set -uo pipefail
cd "$(dirname "$0")/../.." || exit 2

NS=$(php -r '$c = json_decode(file_get_contents("composer.json"), true); echo rtrim(array_key_first($c["autoload"]["psr-4"]), "\\");' 2>/dev/null)
[ -n "$NS" ] || { echo "layers: NO PUDO CORRER — no se pudo leer el namespace PSR-4 de composer.json"; exit 2; }

BASE_BRANCH="${BASE_BRANCH:-${GITHUB_BASE_REF:-main}}"
BASE="origin/$BASE_BRANCH"
git rev-parse -q --verify "$BASE" > /dev/null 2>&1 || BASE="$BASE_BRANCH"

TODO=false
if [ "${1:-}" = "--all" ]; then
    TODO=true
    FILES=$(find src -name '*.php')
elif [ "$#" -gt 0 ]; then
    FILES=$(printf '%s\n' "$@")
else
    FILES=$(bash tools/architecture/cambiados.sh 'src/*.php')
fi

if [ -z "$FILES" ]; then
    echo "layers: no hay archivos de src/ que revisar."
    exit 0
fi

# Deuda preexistente. Se lista por archivo a propósito: una excepción con nombre se puede
# saldar; una categoría («los repositorios de archivos no llevan contrato») se convierte en
# permiso permanente. Una ruta por línea en tools/architecture/excepciones.txt.
EXCEPCIONES=""
[ -f tools/architecture/excepciones.txt ] && EXCEPCIONES=$(grep -vE '^\s*(#|$)' tools/architecture/excepciones.txt | tr -d '\r')

exento() { printf '%s\n' "$EXCEPCIONES" | grep -qxF "$1"; }

# Archivos NUEVOS (agregados en el diff o sin trackear): varias reglas solo aplican sobre
# estos, para que la deuda legada no tumbe un cambio ajeno.
NUEVOS=$(SOLO_NUEVOS=1 bash tools/architecture/cambiados.sh 'src/*.php')

es_nuevo() { printf '%s\n' "$NUEVOS" | grep -qxF "$1"; }

# Líneas agregadas de un archivo: todo el archivo si es nuevo, el diff contra la base si no.
agregadas() {
    if es_nuevo "$1" || ! git ls-files --error-unmatch "$1" > /dev/null 2>&1; then
        cat "$1"
    else
        { git diff -U0 "$BASE...HEAD" -- "$1"; git diff -U0 HEAD -- "$1"; } 2>/dev/null \
            | grep -E '^\+' | grep -vE '^\+\+\+' | sed 's/^+//'
    fi
}

# A13 — lo que un comentario NO lleva: una fecha ISO, una cifra con separador de miles o un
# conteo con su unidad. Es lo que se midió al investigar y caduca en una semana.
A13_RE='\b20[0-9]{2}-[0-9]{2}-[0-9]{2}\b|\b[0-9]{1,3}(\.[0-9]{3})+\b|\b[0-9]{2,}[[:space:]]+(orders?|ordenes|órdenes|rows|filas|cases?|casos|records?|registros?|users?|usuarios)\b'
COMENTARIO='^\s*(\*|//|/\*|#)'

FALLAS=0

reportar() {
    echo "$1  $2"
    echo "        $3"
    FALLAS=$((FALLAS + 1))
}

for f in $FILES; do
    [ -f "$f" ] || continue
    exento "$f" && continue

    importa_factory=$(grep -cF "use $NS\\Factories\\" "$f")
    importa_repo=$(grep -cF "use $NS\\Repositories\\" "$f")
    importa_service=$(grep -cF "use $NS\\Services\\" "$f")
    otro_service=$(grep -F "use $NS\\Services\\" "$f" | grep -vc 'MainConfigurationService')

    lineas=$(agregadas "$f")

    # A11 — las decisiones se citan por su D; el issue vive en el PR y en docs/specs.
    n=$(printf '%s\n' "$lineas" | grep -cE "$COMENTARIO.*#[0-9]{2,}\b")
    [ "${n:-0}" -gt 0 ] && \
        reportar 'A11' "$f" "$n comentario(s) con referencia a un issue (#NN): el codigo cita decisiones (D), no issues"

    # A13 — la evidencia va al PR, a la spec y a _local/.
    n=$(printf '%s\n' "$lineas" | grep -E "$COMENTARIO" | grep -cE "$A13_RE")
    [ "${n:-0}" -gt 0 ] && \
        reportar 'A13' "$f" "$n comentario(s) con evidencia (conteo o fecha): el codigo cita la decision (D); los datos van al PR y a docs/specs"

    case "$f" in
        src/Repositories/*)
            [ "$importa_factory" -gt 0 ] && \
                reportar 'A1' "$f" 'un repositorio no importa un Factory: si necesita otro dominio, lo compone el Service'
            [ "$otro_service" -gt 0 ] && \
                reportar 'B2' "$f" 'un repositorio solo conoce MainConfigurationService'
            grep -qE 'beginTransaction|DB::transaction' "$f" && \
                reportar 'B3' "$f" 'la transaccion vive en el Service, no en el repositorio'
            # Las bases (`*AbstractRepository`) no llevan contrato: lo lleva quien las extiende.
            if grep -qE '^class ' "$f" && ! grep -qE '^class .*implements' "$f"                 && [ "${f%AbstractRepository.php}" = "$f" ]; then
                reportar 'B4' "$f" 'un repositorio concreto implementa su contrato'
            fi
            # B6 — `newQuery()` cierra la línea y cada ->where()/->first() va en la suya; solo
            # ->find($id) queda inline.
            n=$(printf '%s\n' "$lineas" | grep -E 'newQuery\(\)->' | grep -vcE 'newQuery\(\)->find\(')
            [ "${n:-0}" -gt 0 ] && \
                reportar 'B6' "$f" "$n cadena(s) de consulta en una linea: newQuery() cierra la linea y cada ->where()/->first() va en la suya (solo ->find() queda inline)"
            # B5 — solo cuenta ocurrencias NUEVAS contra la rama base, así la deuda legada no
            # tumba cambios ajenos.
            case "$f" in src/Repositories/Database/ORM/*)
                ahora=$(grep -cF 'Manager::connection(' "$f")
                antes=$(git show "$BASE:$f" 2>/dev/null | grep -cF 'Manager::connection(')
                [ "$ahora" -gt "${antes:-0}" ] && \
                    reportar 'B5' "$f" "consulta por conexion directa NUEVA (${antes:-0} -> $ahora Manager::connection): un repositorio ORM usa el query builder del modelo"
                ;;
            esac
            ;;
        src/Services/*)
            # El bucle lee por sustitución de proceso, no por pipe: un `| while` corre en
            # subshell y se traga el contador.
            if es_nuevo "$f"; then
                while IFS= read -r regla; do
                    [ -n "$regla" ] && reportar "${regla%% *}" "$f" "${regla#* }"
                done < <(php tools/architecture/service-rules.php "$f" 2>/dev/null)
            fi
            ;;
        src/Contracts/Repositories/*)
            # A7 — la llave del repositorio es la identidad de la tabla, no la llave por donde
            # se busca. Solo archivos nuevos; sin entity homónima no se opina.
            # Techo conocido: cuenta comillas simples dentro de $keyFields; con dobles no opina.
            if es_nuevo "$f"; then
                aridad=$(grep -oE 'AbstractRepository(Single|Two|Triple|Four|Five)Key' "$f" | head -1)
                entity="src/Entities/${f#src/Contracts/Repositories/}"; entity="${entity%Repository.php}.php"
                if [ -n "$aridad" ] && [ -f "$entity" ]; then
                    n_kf=$(grep -oE 'keyFields\s*=\s*\[[^]]*\]' "$entity" | head -1 | grep -o "'" | wc -l)
                    n_kf=$((n_kf / 2))
                    case "$aridad" in
                        *SingleKey) n_ar=1 ;; *TwoKey) n_ar=2 ;; *TripleKey) n_ar=3 ;;
                        *FourKey) n_ar=4 ;; *FiveKey) n_ar=5 ;;
                    esac
                    [ "$n_kf" -gt 0 ] && [ "$n_kf" -ne "$n_ar" ] && \
                        reportar 'A7' "$f" "la aridad del contrato ($aridad) no es la identidad de la entity (\$keyFields con $n_kf columna(s)): la llave del repositorio es la de la tabla"
                fi
            fi
            ;;
        src/Aggregates/*)
            [ "$importa_factory" -gt 0 ] && \
                reportar 'A2' "$f" 'un Aggregate no importa un Factory'
            { [ "$importa_repo" -gt 0 ] || [ "$importa_service" -gt 0 ]; } && \
                reportar 'B1' "$f" 'un Aggregate no importa Repositories ni Services'
            # A6 — el envoltorio del LISTADO no se escribe: AggregateCollectionWithPaginatedListingAggregate
            # ya recibe (colección, paginado, filtros) y expone sus tres getters. Solo archivos
            # nuevos; un cuarto getter real es la única licencia, y por eso la regla cuenta
            # getters en vez de mirar qué clases nombra el archivo.
            # Los de la raíz de src/Aggregates son los genéricos; la regla mira los de dominio.
            if es_nuevo "$f" && [ "${f%ListingAggregate.php}" != "$f" ]                 && [ "$(dirname "$f")" != "src/Aggregates" ] \
                && [ "$(grep -c 'public function get' "$f")" -le 3 ]; then
                reportar 'A6' "$f" 'el listado devuelve AggregateCollectionWithPaginatedListingAggregate; escribir uno propio exige un cuarto getter que lo justifique'
            fi
            ;;
        src/Entities/*)
            { [ "$importa_repo" -gt 0 ] || [ "$importa_service" -gt 0 ]; } && \
                reportar 'A3' "$f" 'una Entity resuelve por Factory, nunca importando un Repository o Service'
            # T2 — defecto de AUSENCIA: AbstractEntity descarta campos desconocidos en silencio.
            if es_nuevo "$f" && grep -qE 'extends AbstractEntity\b' "$f" \
                && ! grep -qE '^abstract class' "$f"; then
                grep -qE '(public|protected) (array )?\$required' "$f" || \
                    reportar 'T2' "$f" 'una Entity nueva declara $required (AbstractEntity descarta campos desconocidos sin error)'
                grep -qE '(public|protected) (array )?\$keyFields' "$f" || \
                    reportar 'T2' "$f" 'una Entity nueva declara $keyFields con su identidad'
            fi
            ;;
        src/ValueObjects/*)
            { [ "$importa_repo" -gt 0 ] || [ "$importa_service" -gt 0 ]; } && \
                reportar 'A4' "$f" 'un ValueObject no importa Repositories ni Services'
            ;;
    esac

    grep -qF 'declare(strict_types' "$f" && \
        reportar 'A5' "$f" 'esta libreria no usa declare(strict_types=1)'

    # F1 — `get` es lo que sale hacia afuera y controla el error lanzando la excepción. Solo
    # contratos y repositorios, y solo líneas agregadas: el getter de una entidad
    # (`getUuid(): ?string`) es una propiedad, no una búsqueda.
    case "$f" in src/Contracts/Repositories/*|src/Repositories/*)
        while IFS= read -r linea; do
            [ -n "$linea" ] && reportar 'F1' "$f" "$linea — un metodo de busqueda que puede devolver null se llama find, no get"
        done < <(printf '%s\n' "$lineas" \
            | grep -E 'public function get[A-Za-z0-9_]*\(.*\)[[:space:]]*:[[:space:]]*\?' | sed 's/^[[:space:]]*//')
        ;;
    esac
done

# N — nombres: la misma regla que spec-check SP7 corre sobre la spec (naming.sh). Archivos
# nuevos completos; en los modificados, solo los métodos y $variables agregados.
if [ -f tools/architecture/naming.sh ] && [ "$TODO" = false ]; then
    NUEVOS_N=$(for n in $NUEVOS; do exento "$n" || printf '%s\n' "$n"; done)
    AGREGADOS_N=""
    for f in $FILES; do
        es_nuevo "$f" && continue
        exento "$f" && continue
        git ls-files --error-unmatch "$f" > /dev/null 2>&1 || continue
        a=$(agregadas "$f" | grep -oE 'public function [a-zA-Z0-9_]+|\$[a-zA-Z_][A-Za-z0-9_]+' \
            | sed 's/^public function //' | grep -vE '^__|^\$this$')
        [ -n "$a" ] && AGREGADOS_N=$(printf '%s\n%s\n' "$AGREGADOS_N" "$a")
    done
    N_OUT=""
    [ -n "$NUEVOS_N" ] && N_OUT=$(bash tools/architecture/naming.sh --files $NUEVOS_N 2>/dev/null | grep -E '^N[0-9]')
    AGREGADOS_N=$(printf '%s\n' "$AGREGADOS_N" | sed '/^$/d' | sort -u)
    [ -n "$AGREGADOS_N" ] && N_OUT=$(printf '%s\n%s\n' "$N_OUT" \
        "$(printf '%s\n' "$AGREGADOS_N" | bash tools/architecture/naming.sh --names 2>/dev/null | grep -E '^N[0-9]')")
    N_OUT=$(printf '%s\n' "$N_OUT" | sed '/^$/d')
    if [ -n "$N_OUT" ]; then
        printf '%s\n' "$N_OUT"
        FALLAS=$((FALLAS + $(printf '%s\n' "$N_OUT" | wc -l)))
    fi
fi

# A13 también sobre los tests tocados: el docblock de un test es un comentario más.
if [ "$TODO" = false ]; then
    for f in $(bash tools/architecture/cambiados.sh 'tests/*.php'); do
        [ -f "$f" ] || continue
        n=$(agregadas "$f" | grep -E "$COMENTARIO" | grep -cE "$A13_RE")
        [ "${n:-0}" -gt 0 ] && \
            reportar 'A13' "$f" "$n comentario(s) con evidencia (conteo o fecha) en un test: los datos van al PR y a docs/specs"
    done
fi

TOTAL=$(printf '%s\n' "$FILES" | sed '/^$/d' | wc -l)
echo "layers: $TOTAL archivos · $FALLAS violacion(es)"

[ "$FALLAS" -gt 0 ] && exit 1
exit 0
