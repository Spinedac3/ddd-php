# /implementar <n> — lleva un issue de 7 ejes hasta su recibo

Se puede correr **muchas veces**: cada vez calcula dónde quedó el trabajo y sigue por la pieza
siguiente. La spec canónica ES el cuerpo del issue; este comando no inventa alcance.

## Paso 1 — Leé el issue y armá el mapa

`$ARGUMENTS` es `<n>` (issue de este repo) o `<owner/repo>#<n>` cuando el hogar de la spec vive
en otro repo de la cadena.

**Bajá el dump: `bash tools/harness/spec-dump.sh <owner/repo> <n>`** → escribe
`docs/specs/<n>/issue.md` (encabezado + el cuerpo completo; el formato lo pone el script, no
vos). Ese dump ES la spec que se congela: se re-baja en CADA fase, y `recibo.sh` corta si el
`fetched_at` no es del día.

De ahí salen dos cosas y ninguna se inventa:

- **Eje 0** → qué piezas se tocan y cuáles NO, con motivo.
- **«Issues, ramas y orden de PRs»** → repo + número + rama de cada pieza, y el orden.

Si el issue no tiene spec de 7 ejes, PARÁ y decilo — este comando no aplica (la plantilla para
armarla vive en `docs/plantilla-7ejes.md`).

## Paso 2 — Calculá el estado. De instrumentos, nunca de memoria

| pregunta | evidencia |
|---|---|
| ¿ya está cerrada esta pieza? | `_local/corridas/<repo>-<n>/<repo>/recibo.json` y su `estado` |
| ¿quedó a medio camino? | hay `rojo-corridas.log` pero no `recibo.json` |
| ¿tiene PR? | `gh pr list --head <rama>` |

**Un repo sin clonar es un bloqueo, no un descubrimiento**: decilo antes de empezar.

## Paso 3 — Mostrá el estado y elegí la siguiente pieza

Reportá el estado completo antes de tocar nada:

```
issue <n> — <título>
  library            ✔ APROBABLE   PR #NN
  api                ▸ SIGUIENTE   rama <n>-<slug>
  web                — no se toca (eje 0)
```

## Paso 4 — Leé la doctrina del repo de ESA pieza

**Antes de abrir un archivo de código.** Sólo se inyecta el `CLAUDE.md` del repo donde arrancó
la sesión; entrar con `cd` a otro no carga el suyo y nada avisa que estás trabajando a ciegas.

## Paso 5 — Rama

`git fetch && git checkout <rama de la pieza>`. Si no existe, se crea desde la base con el
nombre que dice la spec.

## Paso 6 — FASE ROJA

Los tests del eje 6 de esa pieza, escritos DESDE la spec. Si los archivos existen, se
EXTIENDEN. Asserts de VALOR exacto; jamás `assertNotNull`/`assertInstanceOf` como único oráculo.

```
RECIBO_MODELO=<modelo-de-la-sesion> \
  bash tools/harness/recibo.sh rojo <repo>-<n> docs/specs/<n>/issue.md <archivos de test...>
```

Si la spec declara sondas MANUALES (seam decisorio de frontend, criterios de UI): copiá al
recibo QUÉ debe validar el dev — `RECIBO_VALIDAR="<la línea del seam de la spec>"` — y **seguí:
no frenes la pieza esperando el recorrido.**

`RECIBO_MODELO` SIEMPRE: bash no puede detectarlo y sin la variable el recibo queda
`no-registrado`.

**TODOS deben fallar.** Uno que pasa en rojo está mal escrito: arreglalo antes de seguir. Tests
agregados después → re-correr rojo ANTES de implementarlos.

En un repo sin phpunit, los argumentos tras la spec son UN comando de smoke (curl, `npm test`):
en rojo debe fallar, en verde pasar.

## Paso 7 — IMPLEMENTACIÓN

Con el agente backend del repo (`ddd-backend`). Guiada por los ejes 1-5, respetando el
**NO-se-toca** del eje 2 al pie de la letra. DDL sólo se PROPONE — no se corre sin OK
explícito, y siempre contra la base DEV.

**Qué se le pasa al agente: el dump congelado + la doctrina de su repo — nada resumido.**
Resumirle la spec es re-narrarla: el sha del recibo deja de certificar lo que el implementador
leyó de verdad.

**Homónimo resuelto implementando** — una palabra del dominio que resultó ser otra tabla de la
que parecía — → fila a `docs/glosario.md` en ese mismo PR, con su «NO es» y su evidencia. Esta
regla la ejecuta el orquestador cuando el subagente reporta el hallazgo o cuando lo ve en el
diff: una regla en el prompt del agente se cita y se viola igual.

## Paso 8 — FASE VERDE

```
RECIBO_MODELO=<modelo-de-la-sesion> \
  bash tools/harness/recibo.sh verde <repo>-<n> docs/specs/<n>/issue.md <archivos de test...>
```

Exigido: `nunca_rojos` vacío y estado APROBABLE. DEGRADADO/FALLANDO → el recibo dice por qué;
arreglá y repetí. **INCONCLUSO** = un verificador no pudo correr (phpcs sin binario, sin
`vendor/`): no es verde ni rojo, **no se aprueba ni se explica** — se hace correr y se repite
el recibo. Correr SOLO los tests tocados, nunca la suite entera.

## Paso 8b — TRIANGULAR (con el verde en la mano, antes del recibo final)

Los tests del eje 6 los escribió el grill contra el DISEÑO, cuando este código no existía.
Ahora el código existe, y nadie lo interrogó a él: el `if` que elegiste, el orden de tus
validaciones, la rama que agregaste.

**Buscá a propósito UN caso que rompa lo que acabás de escribir** — leyendo la implementación,
no la spec: el argumento en el borde del guard, el NULL en la columna nullable que tu getter no
espera, la clave repetida que revienta `EntityCollection::add()`.

- Lo encontraste y el test nuevo sale ROJO → es un defecto real: entra al loop (rojo→verde con
  `recibo.sh`) como cualquier otro test.
- El caso más filoso que se te ocurrió sale VERDE → el test queda igual (documenta el borde) y
  en el PR una línea: qué caso se buscó.
- Es una pregunta de ALCANCE y no de código → no es triangular: va como DECISIÓN al issue.

Uno basta; más solo si el primero salió rojo. Y jamás se salta: una suite puede salir verde con
todos los tests de la spec y el defecto real adentro, porque todos interrogan lo prometido y
ninguno lo construido.

## Paso 9 — CIERRE de la pieza

Commits en su rama, en el formato del repo (`:gitmoji_shortcode: Asunto corto`, sin trailers de
asistente). La evidencia se commitea con el cambio: `docs/specs/<n>/recibo.json` y el dump
`docs/specs/<n>/issue.md`. Los logs de la corrida quedan en `_local/`: son ruido de ejecución,
no evidencia.

**La evidencia no entra al código — ni en comentarios, ni en docblocks de tests, ni como
fixture.** Lo que el grill y la triangulación midieron (conteos, fechas, el registro vivo que
disparó el caso) va al PR, a la spec y a `_local/corridas/`; el comentario cita la decisión
(`D9`). Lo mira el gate `A13`, y **antes de presentar el cierre se lee el diff buscándolo a
mano igual**: el gate no ve un fixture.

**NO pushees ni crees el PR**: presentá `recibo.json` + `git log --oneline` + resumen del diff,
y esperá el OK explícito. Con el OK: push y **`/pr` directo**.

Después volvé al paso 2: si la cadena tiene más piezas, sigue.

## Al terminar cada pieza, reportá estas mediciones

- Fallas de gate en primera pasada (cuántas y cuáles).
- `nunca_rojos` (los tests nuevos deben ser 0).
- Todo campo del recibo que hayas completado narrando en vez de sacarlo de un instrumento —
  cada uno es un defecto del kit y se reporta, no se calla.
- Hallazgos del dev en la validación manual (cuántos y cuáles).
- **Última pieza cerrada**: revisá las DURADERAS de la spec — ¿alguna es un homónimo
  (→ `docs/glosario.md`) o una regla de la casa (→ `CLAUDE.md`)? La promoción no ocurre sola.

## Cuando el cambio llega sobre algo YA entregado

- **Issue abierto** (todavía no mergeado): la spec se edita, el recibo sale DEGRADADO y la
  causa se escribe en el PR.
- **Issue cerrado**: **no se edita nunca.** El cambio nace como issue NUEVO que lo cita y
  hereda su mapa de decisiones — «Continúa #12; vigentes sus DURADERAS: D1 · D2 — D3 la
  reemplaza este issue». Un issue = una spec = un sha = sus recibos.
- Cambio de menos de 2 archivos sin decisión nueva: sin ceremonia — bug con su recibo y una
  línea en el issue que lo cita.

## Reglas fijas

- Un archivo de test a la vez, trabajo serial.
- Lo que la spec no dice y tuviste que decidir → se lista como DECISIÓN, jamás se rellena en
  silencio.
- La spec es inmutable tras el rojo (su sha viaja en el recibo). Si un hallazgo exige
  cambiarla, se PARA y se consulta; el cambio lleva causa escrita en el PR.
- **Lo del flujo va en su propio PR; lo del issue, en el suyo.** Doctrina, gates y comandos
  nunca entran en el PR de la feature.
