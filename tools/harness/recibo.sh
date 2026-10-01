#!/bin/bash
# Recibo: la evidencia viaja con el PR y sale de instrumentos, nunca del relato del agente.
# Registra VALORES (no exit codes) y PUEDE registrar el rojo — un instrumento que solo
# registra éxitos siempre reporta éxito.
#
# Uso (desde cualquier lado; el repo objetivo va en RECIBO_REPO, default este repo):
#   bash recibo.sh rojo  <issue> <spec.md> <tests...>  # los tests de la spec se ven fallar
#   bash recibo.sh verde <issue> <spec.md> <tests...>  # tests + gates -> recibo.json
#   RECIBO_REPO=../api bash recibo.sh ...              # recibo del PR de otro repo de la cadena
#   bash recibo.sh autochequeo                         # prueba la guarda de frescura
#
# <spec.md> es el DUMP del issue (docs/specs/<n>/issue.md, bajado por API con spec-dump.sh),
# no una copia a mano: lleva "fetched_at:" y se re-baja en CADA fase. Sin eso el sha congela
# un texto que el issue ya no tiene.
#
# Repos SIN phpunit: los argumentos tras <spec.md> son UN COMANDO de smoke (curl, npm test…).
# Misma disciplina: en rojo el comando debe FALLAR (exit != 0); en verde debe pasar. El recibo
# registra cmd, exit y última línea como valor medido.
#
# La spec es UNA por feature: todos los repos congelan el mismo archivo; el sha ata todo.
# Si la spec cambia entre rojo y verde, el recibo queda DEGRADADO y la causa se escribe en el PR.
set -u
S="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$S/../.." && pwd)"  # raíz del repo donde vive el kit (tools/harness)
REPO="${RECIBO_REPO:-$ROOT}"
FASE="${1:-}"

# GUARDA DE FRESCURA. Si alguien edita el issue y el dump no se re-baja, el sha certifica un
# texto que ya nadie lee. Por eso el dump lleva "fetched_at: <fecha>" y acá se exige que sea
# de HOY, en cada fase.
verificar_frescura() { # $1 = archivo de spec; $2 = fecha de hoy (inyectable para el autochequeo)
    local f="$1" hoy="${2:-$(date +%F)}" cuando
    cuando=$(grep -m1 -E "^fetched_at:" "$f" | tr -d '\r' | awk '{print $2}' | cut -dT -f1)
    if [ -z "$cuando" ]; then
        echo "ADVERTENCIA: la spec no trae 'fetched_at' — no hay forma de saber si el issue cambio despues de esta copia."
        return 0
    fi
    if [ "$cuando" != "$hoy" ]; then
        echo "el dump del issue es del $cuando y hoy es $hoy: re-bajalo antes de congelar — el issue pudo cambiar." >&2
        return 1
    fi
    return 0
}

if [ "$FASE" = "autochequeo" ]; then
    T=$(mktemp -d); F=0
    printf 'titulo: x\n' > "$T/sin.md"
    printf 'fetched_at: 2020-01-01\n' > "$T/viejo.md"
    printf 'fetched_at: 2030-12-31T09:00\n' > "$T/hoy.md"
    verificar_frescura "$T/sin.md" 2030-12-31 > /dev/null || { echo "FALLA: sin fetched_at debe pasar con advertencia"; F=1; }
    verificar_frescura "$T/viejo.md" 2030-12-31 2>/dev/null && { echo "FALLA: un dump viejo debe cortar"; F=1; }
    verificar_frescura "$T/hoy.md" 2030-12-31 > /dev/null || { echo "FALLA: un dump de hoy debe pasar"; F=1; }
    rm -rf "$T"
    [ "$F" -eq 0 ] && echo "autochequeo ok: la guarda de frescura corta el dump viejo y deja pasar el del dia"
    exit "$F"
fi

if [ $# -lt 4 ]; then
    echo "uso: recibo.sh rojo|verde <issue> <spec.md> <tests...>"
    exit 2
fi
ISSUE="$2"
SPEC="$3"
shift 3
TESTS=("$@")

[ -f "$SPEC" ] || { echo "spec inexistente: $SPEC"; exit 2; }
# Ruta absoluta ANTES del cd al repo — una spec relativa se rompía tras el cd y el sha
# quedaba vacío en silencio.
SPEC="$(cd "$(dirname "$SPEC")" && pwd)/$(basename "$SPEC")"
cd "$REPO" || { echo "repo inexistente: $REPO"; exit 2; }
RNAME="$(basename "$REPO")"
# <ISSUE> nombra la carpeta de la corrida: <repo-hogar>-<n> — el número pelado choca entre
# repos cuando la cadena tiene varios. _local/corridas nace sola (mkdir -p) y git la ignora.
DIR="${RECIBO_CORRIDAS:-$ROOT/_local/corridas}/$ISSUE/$RNAME"
mkdir -p "$DIR"

json_str() { php -r 'echo json_encode($argv[1], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);' "$1"; }
json_lineas() { # archivo de lineas -> array JSON
    php -r 'echo json_encode(is_file($argv[1]) ? array_values(array_filter(file($argv[1], FILE_IGNORE_NEW_LINES))) : [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);' "$1"
}

# El modelo no se puede detectar desde bash (la sesión no lo expone en el entorno): viaja en
# RECIBO_MODELO o el recibo queda cojo.
if [ -z "${RECIBO_MODELO:-}" ]; then
    echo "ADVERTENCIA: RECIBO_MODELO sin declarar — el recibo dira 'no-registrado'."
fi

verificar_frescura "$SPEC" || exit 2
# Un issue CERRADO es inmutable: si su texto cambió, no es un cambio de alcance con causa, es
# una violación — el cambio va en un issue NUEVO que cite a este.
ISSUE_CERRADO=false
grep -qiE "^estado: *(cerrado|closed)" "$SPEC" && ISSUE_CERRADO=true
# El sha cubre el CUERPO del dump, no el frontmatter: la guarda de frescura obliga a re-bajar
# cada día, y `fetched_at` cambiaría el sha con el texto idéntico.
sha_cuerpo() { awk 'c>=2{print} /^---$/{c++}' "$1" | tr -d '\r' | sha256sum | cut -d' ' -f1; }
SPEC_SHA=$(sha_cuerpo "$SPEC")
COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo sin-git)
RAMA=$(git branch --show-current 2>/dev/null || echo "")
FECHA=$(date +%Y-%m-%dT%H:%M:%S)
PHPV=$(php -r 'echo PHP_VERSION;')

# Setup de tests = binario + configuración. El nombre del config no es uno solo: mirar solo
# `phpunit.xml` hace que un CLON FRESCO caiga a modo smoke EN SILENCIO.
HAVE_PHPUNIT=false
PHPUNIT_CONF=""
for c in phpunit.xml phpunit.xml.dist phpunit.dist.xml; do
    [ -f "$c" ] && { PHPUNIT_CONF="$c"; break; }
done
if [ -f "vendor/bin/phpunit" ] && [ -n "$PHPUNIT_CONF" ]; then
    HAVE_PHPUNIT=true
fi

# Una corrida por ruta: PHPUnit 9 acepta UNA sola ruta y descarta las demás en silencio — con
# varias rutas el recibo certificaba solo la primera. Cada corrida deja su junit ($1.N).
correr_tests() { # $1 = prefijo del xml de salida; deja el log en $DIR. Devuelve 1 si alguna falla.
    local i=0 rc=0 t
    rm -f "$1".*
    : > "$DIR/phpunit-$FASE.log"
    for t in "${TESTS[@]}"; do
        i=$((i + 1))
        vendor/bin/phpunit -c "$PHPUNIT_CONF" --no-coverage --log-junit "$1.$i" "$t" >> "$DIR/phpunit-$FASE.log" 2>&1 || rc=1
    done
    return $rc
}

correr_smoke() { # corre el comando de smoke; deja log y devuelve su exit
    "${TESTS[@]}" > "$DIR/smoke-$FASE.log" 2>&1
}

leer_junit() { # $1 = prefijo de los xml; emite el JSON del parser o corta ruidoso si falta alguno
    local r i=0 xmls=()
    for _ in "${TESTS[@]}"; do i=$((i + 1)); xmls+=("$1.$i"); done
    r=$(php "$S/recibo-junit.php" "${xmls[@]}")
    if printf '%s' "$r" | grep -q '"error"'; then
        echo "la corrida no entrego junit legible — esto NO es una fase valida. Ultimas lineas:" >&2
        tail -5 "$DIR/phpunit-$FASE.log" >&2
        exit 2
    fi
    printf '%s' "$r"
}

case "$FASE" in
rojo)
    # La forma se verifica ANTES de congelar: una spec malformada congelada se implementa
    # entera y truena recién en verde.
    if [ -f "$S/spec-check.sh" ] && ! bash "$S/spec-check.sh" "$SPEC"; then
        echo "rojo: la spec NO pasa la forma — arreglala en el ISSUE, re-baja el dump (spec-dump.sh) y volve. No se congela una spec malformada." >&2
        exit 1
    fi
    # La spec se congela en el PRIMER rojo de este repo; corridas siguientes acumulan.
    if [ ! -f "$DIR/spec-congelada.sha" ]; then
        echo "$SPEC_SHA" > "$DIR/spec-congelada.sha"
        cp "$SPEC" "$DIR/spec-congelada.md"
    fi
    if [ "$HAVE_PHPUNIT" = true ]; then
        correr_tests "$DIR/rojo.xml" || true
        # el exit de leer_junit muere en el subshell — propagarlo o la falla cascadea
        R=$(leer_junit "$DIR/rojo.xml") || exit 2
        T=$(php -r 'echo json_decode($argv[1], true)["tests"];' "$R")
        F=$(php -r 'echo json_decode($argv[1], true)["fallas"];' "$R")
        php -r 'foreach (json_decode($argv[1], true)["fallados"] as $n) echo $n, "\n";' "$R" >> "$DIR/rojos.txt"
        sort -u "$DIR/rojos.txt" -o "$DIR/rojos.txt"
        echo "$FECHA commit=$COMMIT tests=$T fallas=$F" >> "$DIR/rojo-corridas.log"
        echo "rojo [$RNAME]: $T tests, $F vistos fallar (acumulado: $(wc -l < "$DIR/rojos.txt"))"
        if [ "$F" -eq 0 ]; then
            echo "ADVERTENCIA: 0 fallas en fase rojo — un test que no se vio fallar no existe."
            echo "$FECHA ADVERTENCIA-0-FALLAS" >> "$DIR/rojo-corridas.log"
        fi
    else
        # Modo smoke (repo sin phpunit): el comando debe FALLAR antes de implementar.
        correr_smoke && E=0 || E=$?
        echo "$FECHA commit=$COMMIT smoke_exit=$E cmd=${TESTS[*]}" >> "$DIR/rojo-corridas.log"
        if [ "$E" -ne 0 ]; then
            echo "si" > "$DIR/smoke-visto-fallar.txt"
            echo "rojo-smoke [$RNAME]: visto fallar (exit $E) — correcto antes de implementar"
        else
            echo "ADVERTENCIA: el smoke NO fallo en fase rojo — un check que no se vio fallar no existe."
            echo "$FECHA ADVERTENCIA-SMOKE-0" >> "$DIR/rojo-corridas.log"
        fi
    fi
    ;;

verde)
    [ -f "$DIR/rojo-corridas.log" ] || { echo "sin fase rojo no hay recibo — correr 'rojo' primero"; exit 2; }

    SPEC_CAMBIADA=false
    CONGELADA_SHA=$(cat "$DIR/spec-congelada.sha")
    [ "$SPEC_SHA" != "$CONGELADA_SHA" ] && SPEC_CAMBIADA=true

    SMOKE_JSON="null"
    if [ "$HAVE_PHPUNIT" = true ]; then
        correr_tests "$DIR/verde.xml" || true
        RV=$(leer_junit "$DIR/verde.xml") || exit 2
        TV=$(php -r 'echo json_decode($argv[1], true)["tests"];' "$RV")
        FV=$(php -r 'echo json_decode($argv[1], true)["fallas"];' "$RV")
        php -r 'foreach (json_decode($argv[1], true)["nombres"] as $n) echo $n, "\n";' "$RV" | sort -u > "$DIR/verde-nombres.txt"
        touch "$DIR/rojos.txt"
        comm -23 "$DIR/verde-nombres.txt" "$DIR/rojos.txt" > "$DIR/nunca-rojos-crudo.txt"
        # Solo los tests NUEVOS están obligados a verse fallar: uno que ya existía en la base
        # nace verde por definición, y contarlo degradaría TODO recibo que extienda un archivo
        # de tests. Si el mapeo de clase a archivo no resuelve, el test cuenta como nuevo: el
        # medidor falla hacia degradar, nunca hacia callar.
        BASE_BRANCH="${BASE_BRANCH:-${GITHUB_BASE_REF:-main}}"
        BASE_REF="origin/$BASE_BRANCH"
        git rev-parse -q --verify "$BASE_REF" > /dev/null 2>&1 || BASE_REF="$BASE_BRANCH"
        # El namespace de los tests sale de composer.json (autoload-dev psr-4).
        TESTS_NS=$(php -r '$c = json_decode(file_get_contents("composer.json"), true); echo array_key_first($c["autoload-dev"]["psr-4"] ?? ["" => ""]);' 2>/dev/null)
        > "$DIR/nunca-rojos.txt"
        while IFS= read -r t; do
            [ -z "$t" ] && continue
            metodo="${t##*::}"
            clase="${t%%::*}"
            archivo="tests/$(printf '%s' "${clase#"$TESTS_NS"}" | tr '\\' '/').php"
            if git show "$BASE_REF:$archivo" 2>/dev/null | grep -qE "function[[:space:]]+$metodo\b"; then
                continue
            fi
            # Tests heredados: el método puede vivir en una base genérica y no en el archivo
            # del test. Si existe en CUALQUIER archivo de tests de la base, es preexistente.
            # Techo conocido: un test nuevo que se llame idéntico a uno viejo escapa a la
            # exigencia de rojo; el reverso (degradar por cada test heredado) es peor.
            if git grep -qE "function[[:space:]]+$metodo\b" "$BASE_REF" -- tests/ 2>/dev/null; then
                continue
            fi
            printf '%s\n' "$t" >> "$DIR/nunca-rojos.txt"
        done < "$DIR/nunca-rojos-crudo.txt"
        TESTS_JSON="{\"corridos\":$TV,\"fallas\":$FV,\"nunca_rojos\":$(json_lineas "$DIR/nunca-rojos.txt")}"
    else
        correr_smoke && SE=0 || SE=$?
        SMOKE_RES=$(tail -1 "$DIR/smoke-verde.log")
        SMOKE_JSON="{\"cmd\":$(json_str "${TESTS[*]}"),\"exit\":$SE,\"resumen\":$(json_str "$SMOKE_RES")}"
        TESTS_JSON="null"
        FV=$SE
        : > "$DIR/nunca-rojos.txt"
    fi

    # Gates del repo: se corren los que existan; los que no, quedan DECLARADOS.
    GATES_JSON=""
    GATE_FALLO=0
    GATE_NO_CORRIO=0
    > "$DIR/no-revisado.txt"
    for g in loads layers schema phpcs; do
        if [ -f "tools/architecture/$g.sh" ]; then
            OUT=$(bash "tools/architecture/$g.sh" 2>&1)
            E=$?
            # exit 2 = el gate NO PUDO correr (phpcs sin binario, loads sin vendor). Un negativo
            # de una herramienta que no corrió no es un negativo: es INCONCLUSO.
            if [ "$E" -eq 2 ]; then
                GATE_NO_CORRIO=1
                echo "gate $g: NO PUDO correr (exit 2) — no dice ni verde ni rojo" >> "$DIR/no-revisado.txt"
            elif [ "$E" -ne 0 ]; then
                GATE_FALLO=1
            fi
            RES=$(printf '%s\n' "$OUT" | tail -1)
            printf '%s\n' "$OUT" > "$DIR/gate-$g.log"
            [ -n "$GATES_JSON" ] && GATES_JSON="$GATES_JSON,"
            GATES_JSON="$GATES_JSON\"$g\":{\"exit\":$E,\"resumen\":$(json_str "$RES")}"
        else
            echo "gate $g: no existe en $RNAME" >> "$DIR/no-revisado.txt"
        fi
    done

    # Gate de forma de la spec: corre contra el dump, viva donde viva la pieza (el script es
    # del kit, $S). Mismo trato que los gates: 2 = INCONCLUSO, 1 = FALLANDO.
    if [ -f "$S/spec-check.sh" ]; then
        OUT=$(bash "$S/spec-check.sh" "$SPEC" 2>&1)
        E=$?
        if [ "$E" -eq 2 ]; then
            GATE_NO_CORRIO=1
            echo "gate spec: NO PUDO correr (exit 2) — no dice ni verde ni rojo" >> "$DIR/no-revisado.txt"
        elif [ "$E" -ne 0 ]; then
            GATE_FALLO=1
        fi
        printf '%s\n' "$OUT" > "$DIR/gate-spec.log"
        RES=$(printf '%s\n' "$OUT" | tail -1)
        [ -n "$GATES_JSON" ] && GATES_JSON="$GATES_JSON,"
        GATES_JSON="$GATES_JSON\"spec\":{\"exit\":$E,\"resumen\":$(json_str "$RES")}"
    else
        echo "gate spec: no existe spec-check.sh en el kit" >> "$DIR/no-revisado.txt"
    fi

    # Sondas manuales (seam decisorio de frontend, criterios de UI): el recibo NO espera el
    # recorrido — le deja claro AL DEV qué debe validar (RECIBO_VALIDAR, copiado de la línea
    # del seam de la spec, no narrado).
    [ -z "${RECIBO_VALIDAR:-}" ] && echo "validacion del dev: sin lista — si la spec exige recorrido critico o CA manual, este recibo no dice que validar" >> "$DIR/no-revisado.txt"
    echo "triangular: sin instrumento que lo verifique — el PR debe decir que caso se busco contra lo construido" >> "$DIR/no-revisado.txt"
    echo "si el cambio hace lo que se pidio: ningun gate lo mide — lo revisa el humano contra la spec" >> "$DIR/no-revisado.txt"
    echo "la coherencia entre los PRs de la cadena: todavia no hay gate que la mire" >> "$DIR/no-revisado.txt"
    echo "tests fuera de la lista de la spec: aca no corren — la suite completa la corre el pipeline del PR" >> "$DIR/no-revisado.txt"
    echo "los verificadores del kit: no hay manifiesto que certifique que cada gate se vio fallar tras su ultimo cambio" >> "$DIR/no-revisado.txt"
    [ "$HAVE_PHPUNIT" = false ] && echo "phpunit: no existe en $RNAME — smoke generico en su lugar" >> "$DIR/no-revisado.txt"

    # Estado: FALLANDO (tests o gates en rojo) > INCONCLUSO (un verificador no pudo correr)
    # > DEGRADADO (nunca-rojos, spec cambiada) > APROBABLE.
    # El recibo registra el rojo, no lo esconde — y no llama rojo a lo que no se midió.
    ESTADO="APROBABLE"
    NOTAS="$DIR/notas.txt"
    > "$NOTAS"
    [ -s "$DIR/nunca-rojos.txt" ] && { ESTADO="DEGRADADO"; echo "hay tests NUEVOS que nacieron en verde: nunca se los vio fallar, asi que todavia no prueban nada (lista en nunca_rojos) — causa del cambio" >> "$NOTAS"; }
    if [ "$HAVE_PHPUNIT" = false ] && [ ! -f "$DIR/smoke-visto-fallar.txt" ]; then
        ESTADO="DEGRADADO"
        echo "el chequeo de humo nunca se vio fallar: no sabemos si es capaz de detectar algo — causa del cambio" >> "$NOTAS"
    fi
    [ "$SPEC_CAMBIADA" = true ] && { ESTADO="DEGRADADO"; echo "la spec cambio entre el rojo y el verde: la causa debe explicarse en el PR — causa del cambio" >> "$NOTAS"; }
    { [ "$SPEC_CAMBIADA" = true ] && [ "$ISSUE_CERRADO" = true ]; } && { ESTADO="FALLANDO"; echo "el issue esta CERRADO y su texto cambio: viola la inmutabilidad — el cambio va en un issue NUEVO que cite a este" >> "$NOTAS"; }
    [ "$GATE_NO_CORRIO" -eq 1 ] && { ESTADO="INCONCLUSO"; echo "un verificador NO PUDO correr (cual, en no_revisado): este recibo no dice verde ni rojo sobre lo que ese gate mira — se resuelve y se repite, no se aprueba" >> "$NOTAS"; }
    { [ "$FV" -gt 0 ] || [ "$GATE_FALLO" -eq 1 ]; } && { ESTADO="FALLANDO"; echo "tests o gates en rojo" >> "$NOTAS"; }

    cat > "$DIR/recibo.json" <<EOF
{
  "recibo": "v0",
  "leeme": "APROBABLE = todo lo medido en verde · DEGRADADO = aprobable si las causas de 'notas' convencen · INCONCLUSO = un verificador no pudo correr: no se sabe, se resuelve y se repite · FALLANDO = no aprobar. 'no_revisado' lista lo que este recibo NO mira y quien si lo cubre.",
  "issue": $(json_str "$ISSUE"),
  "repo": $(json_str "$RNAME"),
  "rama": $(json_str "$RAMA"),
  "fecha": $(json_str "$FECHA"),
  "commit": $(json_str "$COMMIT"),
  "php": $(json_str "$PHPV"),
  "modelo": $(json_str "${RECIBO_MODELO:-no-registrado}"),
  "validacion_del_dev": $([ -n "${RECIBO_VALIDAR:-}" ] && json_str "PENDIENTE de recorrer por el dev: $RECIBO_VALIDAR" || echo null),
  "spec": {
    "sha256": $(json_str "$CONGELADA_SHA"),
    "cambiada": $SPEC_CAMBIADA
  },
  "fase_rojo": {
    "corridas": $(grep -c 'commit=' "$DIR/rojo-corridas.log"),
    "tests_vistos_fallar": $(json_lineas "$DIR/rojos.txt")
  },
  "tests": $TESTS_JSON,
  "smoke": $SMOKE_JSON,
  "gates": {$GATES_JSON},
  "estado": $(json_str "$ESTADO"),
  "notas": $(json_lineas "$NOTAS"),
  "no_revisado": $(json_lineas "$DIR/no-revisado.txt")
}
EOF
    # La evidencia se commitea en el repo DUEÑO del cambio. La carpeta de docs es por número
    # de issue (docs/specs/12), no por nombre de corrida (ddd-php-12).
    IID="${ISSUE##*-}"
    mkdir -p "docs/specs/$IID"
    cp "$DIR/recibo.json" "docs/specs/$IID/recibo.json"
    echo "recibo [$RNAME]: $ESTADO — $REPO/docs/specs/$IID/recibo.json (logs en $DIR)"
    cat "$DIR/recibo.json"
    echo ""
    echo "-> pegar el bloque en la descripcion del PR (fence \`\`\`json)."
    case "$ESTADO" in FALLANDO|INCONCLUSO) exit 1 ;; esac
    exit 0
    ;;

*)
    echo "uso: recibo.sh rojo|verde <issue> <spec.md> <tests...>"
    exit 2
    ;;
esac
