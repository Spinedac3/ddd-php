# Spec de feature — issue #<N> «<título del issue>»

estado: BORRADOR | APROBADA por ___ el ___
(el sha256 lo congela `recibo.sh rojo`; cambiarla después exige causa escrita en el PR)

> Un ejemplo completo y llenado de esta plantilla: el [issue #3](https://github.com/Spinedac3/ddd-php/issues/3) de este repositorio.

**Dónde vive.** El issue de GitHub es la spec canónica (colaboración); lo que se congela y se
commitea es el **dump del issue bajado por API** — `docs/specs/<n>/issue.md` (metadatos +
`fetched_at` + cuerpo completo), y **cada repo commitea su propio**
`docs/specs/<n>/recibo.json`. Se congela el dump y no la copia local a propósito: así el sha
certifica el texto que de verdad leyó quien implementó, y una sección que no viajó al issue se
ve sola. La forja puede cambiar o caerse: la conversación se pierde, la evidencia no — git es
la capa durable y el sha es lo que ata las dos.

**Inmutabilidad.** Issue ABIERTO = la spec se edita con causa escrita (el recibo sale
DEGRADADO). Issue CERRADO **no se edita nunca**: el cambio nace como issue NUEVO que lo cita
(«Continúa #12; hereda sus DURADERAS: D1 · D2 — D3 la reemplaza este issue»). Así el recibo
viejo no puede mentir: el texto que certificó ya no puede cambiar.

**La spec es de la FEATURE, no del repo.** Si la cadena tiene varios repos, la misma spec
gobierna los PRs de todos. Cada PR lleva su recibo apuntando al MISMO sha de esta spec.

**Reglas del artefacto — no quitar de la copia:**
- El eje 0 y los 7 ejes SIEMPRE presentes. «No aplica» / «no se toca» es una entrada explícita
  con motivo — una fila ausente invalida la spec (se rinde cuentas por CADA pieza de la cadena,
  no por las que se quiso mencionar; la costura silenciosa entre repos es exactamente el fallo
  que ningún gate por-repo ve).
- Cada valor lleva evidencia VERIFICABLE (`archivo:línea` · tabla del esquema · issue · dump).
  Prosa sin evidencia no cuenta: una explicación no verificable persuade, no calibra.
- **Un referente (FK, catálogo, fuente de un select) solo está «verificado» con sus TRES
  anclas: DATOS** (query contra la tabla viva: conteo + muestra + fecha del último registro,
  pegada en la spec) · **USO** (consumidor actual señalado `archivo:línea`) ·
  **DISCRIMINACIÓN** (todo homónimo o alternativa se abre y se descarta con motivo escrito).
  Citar la entity no es verificar: es leer la forma. Elegir por nombre o por existencia es
  inferencia y se marca **DECISIÓN**.
- **Una spec con pendientes no congela.** Todo «[confirmar]» / «[pendiente]» vivo al publicar
  es una DECISIÓN sin resolver: se resuelve, o se convierte en bloqueo explícito del eje 0.
- Todo hueco que el issue no dice y el agente rellenó solo va marcado **DECISIÓN** — el
  completado silencioso es el modo de fallo por defecto con issues de solo-título.
- La compuerta es ACTIVA: el aprobador confirma o corrige a mano el eje de mayor riesgo y
  resuelve todas las DECISIÓN. Firmar sin editar nada es señal de plantilla mal usada.
- **Un HOGAR, N ejecutores.** La spec y el mapa de decisiones viven en **un solo issue** — el
  hogar, declarado en el eje 0. Los demás issues de la cadena son de EJECUCIÓN: cuerpo corto
  que **cita** al hogar y **jamás copia la spec** — dos copias divergen. **La carpeta
  `docs/specs/<n>/` usa el número del HOGAR** en todos los repos.
- **La spec que se publica al issue lleva TODAS las secciones, `Mapa de decisiones` y
  `Compuerta` incluidas.** El cuerpo del issue es lo único que lee quien implementa. Una
  DECISIÓN que se queda en la copia local es peor que no haberla marcado. Antes de cerrar la
  publicación: `bash tools/harness/spec-check.sh <borrador> <dump>` compara las secciones de
  los dos lados (SP6). La forma de esta plantilla la verifica el mismo gate, que `recibo.sh`
  corre solo en cada fase.

---

## Eje 0 — Mapa de impacto de la cadena (cross-repo)

Toda fila se llena. Las piezas de tu cadena se declaran una por línea en
`tools/harness/cadena.txt`; `spec-check.sh` (SP1) exige una fila por cada una.

> **Cada pieza marcada «sí» obliga a leer la doctrina de ESE repo antes de escribir su parte**
> — su `CLAUDE.md` y sus `.claude/agents/`. Sólo se inyecta la del repo donde arrancó la
> sesión.

| pieza | ¿tocada? | qué cambia / por qué NO se toca |
|---|---|---|
| **library** (este repo) | sí/no | ... |
| **<otra pieza de la cadena>** | sí/no | ... |

### Issues, ramas y orden de PRs

La sección que hace ejecutable la cadena — sin ella el eje 0 dice *qué* se toca pero nadie sabe
*dónde*. Una fila por pieza marcada «sí»:

| pieza | repo e issue | rama | rol |
|---|---|---|---|
| library | `owner/repo#<n>` | `<n>-<slug>` | hogar de la spec |

**Orden:** ... Si una pieza bloquea a otra, se declara en TEXTO en la primera línea del issue
bloqueado («Bloqueado por #NN»), y el enforcement real es físico: la rama sale apilada sobre la
que la bloquea.

---

## 1. Esquema + migración + backfill
- tablas/columnas afectadas: ... [evidencia: query viva a la tabla]
- **por cada FK/referente nuevo, sus tres anclas**: `referente:` tabla exacta y conexión ·
  `datos:` query + conteo + muestra · `uso-actual:` `archivo:línea` del consumidor vivo ·
  `homónimos-descartados:` lista con motivo. Sin las tres la fila queda como DECISIÓN.
- migración: ... | No aplica porque ...
- backfill de datos existentes: ... | No aplica porque ...

## 2. Alcance — y qué NO se toca
- entra en este cambio: ...
- **NO se toca** (lista explícita de archivos/dominios/comportamientos): ...
- **si es un listado: sus campos de búsqueda**, columna por columna, y qué pasa cuando el texto
  no es del tipo de la columna.

## 3. Servicio dueño
- servicio: `src/Services/<Dominio>/<X>Service.php` — [existente | nuevo] [DECISIÓN?]
- **nombres**: identificadores en **inglés**, y el Service se llama por **la cosa que posee**
  (`DriverService` → `Driver`), nunca por el caso de uso ni por el problema del solicitante. Lo
  verifica `spec-check.sh` (SP7) con la **misma regla** que el gate de código.
- transacción: [sí, en el servicio — varias escrituras que caen juntas | no]
- **rechazos: una fila por cada forma de decir que no.** Clase EXACTA y código.

  | qué se rechaza | clase | código |
  |---|---|---|
  | argumento inválido | `InvalidArgumentException` | 406 |
  | no existe lo referenciado | `{Entity}NotFoundException extends NotFoundException` | 404 |
  | el estado rechaza la escritura | `{X}Exception extends ConflictException` | 409 |

  > Las tres bases **se extienden, no se instancian**. Si la spec nombra una base pelada, el
  > eje está mal llenado (SP3).

## 4. Contrato: nuevo vs reuso
- contrato: `src/Contracts/Repositories/<Dominio>/<X>Repository.php` — [reusa | nuevo]
- aridad de clave: [SingleKey | TwoKey | ...] [evidencia: identidad de la tabla]
- behaviors compuestos: [Creatable/Updatable/Deletable — SOLO los que se implementan]
- paginación: [primera forma (extiende PaginatedListRepository, firma fija) | segunda forma
  (método propio con parámetros extra) | no aplica]

## 5. Retorno
- entity/VO **nuevos** con su RUTA completa (`src/Entities/<Dominio>/<X>.php`)
- tipo exacto: [Entity | ?Entity (find) | EntityCollection | PaginatedEntityCollection |
  ValueObjectCollection | SummaryInfo | {X}Aggregate | {X}With{Y}Aggregate | ...]
- semántica find/get: [find* puede devolver null | get* controla el error y lanza
  {Entity}NotFoundException]
- **¿el retorno es un LISTADO que muestra datos de OTRA tabla?** [sí | no → por qué no]

  Si es sí, el default **no se discute y no se reinventa**: el Service recorre la colección
  paginada, resuelve cada FK con el **getter perezoso de la entity** y arma un
  `{X}With{Y}Aggregate` por fila dentro de un `AggregateCollection`, devuelto en
  `AggregateCollectionWithPaginatedListingAggregate`. El N+1 es **deliberado y aceptado**
  porque el listado es paginado. Devolver ids crudos o precargar un diccionario son
  desviaciones: llevan causa escrita en el PR.

## 6. Tests exigidos (de TODA la cadena tocada)

**Primero, el SEAM DECISORIO — una línea, antes del inventario.** El seam es la costura por
donde el sistema se deja observar sin abrirlo; el decisorio es **el punto más alto donde esta
feature se demuestra viva**: si está verde, la feature funciona.

> Seam decisorio: <sonda concreta> ⇒ <valor esperado que solo el referente correcto produce>

Reglas para elegirlo:
- **El más alto que siga siendo barato y determinista.** Preferí la costura que YA existe.
- Si hay que coser una nueva, **en el punto más alto posible** — y coserla es infraestructura:
  issue aparte, que bloquea al de la feature.
- Por defecto: **backend** = el endpoint o servicio responde según su contrato Y devuelve al
  menos un valor que **solo el referente correcto puede producir** (un contrato satisfecho no
  distingue la tabla equivocada — forma no es dato). **Frontend** = el recorrido crítico de la
  pantalla contra el backend de DEV: el test con mock es ciego al drift de contrato por
  construcción. Automatizado si el repo tiene esos rieles; si no, **manual DECLARADO en el
  recibo** — jamás contado como automático.

Cada test nombra el VALOR exacto que asserta. Prohibido como único oráculo: `assertNotNull` /
`assertInstanceOf` / verificar solo que el mock recibió la llamada / presencia pelada en el
DOM. Cada test listado acá tiene que VERSE FALLAR (fase rojo) antes de implementar.

- library:
  - [ ] `tests/Unit/.../XTest.php::testY` — asserta: <valor concreto esperado>

## 7. Criterios de aceptación verificables
Forma: CUANDO <condición> ENTONCES <resultado con valor concreto>. Cada criterio apunta al test
del eje 6 que lo cubre.

- CA-01: CUANDO ... ENTONCES ... [cubierto por 6.x]

---

## Mapa de decisiones

Una línea por decisión, en orden, con su evidencia. **Viaja al issue.**

**Duraderas: <D# · D# · ...>** — las que hereda el issue que continúe éste.

Una decisión es **DURADERA** solo si pasa las TRES preguntas (con la primera que dé no, se
corta): **¿difícil de revertir?** (esquema, datos persistidos, contratos que otros consumen) ·
**¿sorprendente sin contexto?** (¿un dev en 6 meses preguntaría POR QUÉ?) · **¿trade-off
real?** (¿podés nombrar la alternativa perdedora y por qué perdió?). Consistencia greppable:
las filas marcadas DURADERA = los D# de la línea resumen (SP5).

- D1 DURADERA — <decisión> · revertir=<qué cuesta> ✓ · sorprende=<por qué> ✓ · perdedora=<alternativa> ✓
- D2 — <decisión> · sin marca: <motivo en tres palabras> · evidencia: <archivo:línea | query>

**NIEBLA** (se ve venir, todavía no se puede formular con precisión): ...

**FUERA DE ALCANCE** (cerrado; no vuelve a discutirse): ...

---

## Retroalimentación al método (SDD)

**Encabezado FIJO — no se renombra jamás**: es la sección que se recorre transversalmente en
todos los issues para capturar las mejoras del flujo. Una entrada por hallazgo sobre el MÉTODO
(no sobre el dominio): **qué se cazó · cómo entró · quién lo cazó · arreglo y dónde vive ·
estado**. Si no hubo hallazgos, la sección dice «— sin hallazgos —»; su ausencia es SP8.

---

## Riesgo
- **eje de mayor riesgo: <0-7>** — por qué: ...
- riesgo del cambio 0-4 (por QUÉ se toca — config, dinero, datos maestros, cadena entera — no
  por líneas): ...

## Compuerta (llena el humano, a mano)
- [ ] eje de mayor riesgo confirmado o corregido (editado, no solo tildado)
- [ ] **eje 0 leído fila por fila** — las «no se toca» también
- [ ] todas las **DECISIÓN** resueltas (confirmada o corregida, una por una)
- [ ] «NO se toca» del eje 2 leído y completo
- aprobada por: ___ · fecha: ___ · minutos que tomó: ___
