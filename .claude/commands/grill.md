# /grill <premisa> — de la premisa a la spec y el issue, conversando

La entrada del flujo. TODO desarrollo arranca acá: una premisa (frase, párrafo o imagen del
pedido) se convierte en spec de 7 ejes y en issue, ANTES de escribir una línea de código.
Después sigue `/implementar <n>`.

Excepción de ceremonia: bug obvio de <2 archivos → saltá el grill y andá directo a rojo→verde
con recibo (`/implementar` sobre un issue corto).

## Paso 1 — Leé la premisa COMPLETA

Antes de abrir un solo archivo. Si es imagen, describila de vuelta al usuario para confirmar
que la leíste bien.

## Paso 2 — Entrevista con exploración (la compuerta activa ES esta conversación)

Mapeá el trabajo como un **árbol de decisiones**: cada decisión se ramifica en las decisiones
que cuelgan de ella. Trabajá el árbol **en rondas**. La **frontera** es toda decisión cuyos
prerrequisitos ya están resueltos: las preguntas que podés hacer AHORA sin adivinar respuestas
que todavía no escuchaste. Preguntá la frontera entera en una ronda: numerá cada pregunta y dá
tu respuesta recomendada. Después esperá las respuestas del humano antes de la ronda siguiente.

**La ronda se presenta con la herramienta nativa de opciones cuando exista**: hasta 4 preguntas
por tanda (frontera más grande → tandas consecutivas de la misma ronda), cada una con sus
alternativas como opciones — **la recomendada primera, etiquetada `(Recommended)`** — y el
humano responde libre cuando ninguna le sirve. **La etiqueta corta sola no es una opción**: el
cuerpo de la pregunta lleva el contexto entero y cada opción lleva su descripción con la MISMA
sustancia que llevaría en prosa — qué implica elegirla, su evidencia (`archivo:línea` o query),
su costo o consecuencia. Opciones escuetas obligan al humano a re-preguntar, que es pagar la
ronda dos veces. Si una opción no se puede describir así, no está lista.

**Reglas de la ronda en curso:**
- **Al re-presentar, SOLO lo abierto.** Si una ronda vuelve a emitirse (cambio de formato,
  pregunta sin responder, interrupción), las preguntas YA RESPONDIDAS no se re-ofrecen jamás:
  su respuesta está en el árbol.
- **Un pedido de ampliación se RESPONDE, no se re-pregunta.** Si el humano pide más info sobre
  una pregunta, primero la ampliación en PROSA con su evidencia, y después se re-ofrece SOLO
  esa pregunta.
- **Una opción que contradice la doctrina NO se ofrece.** Si la doctrina ya eligió, la decisión
  se DERIVA y se confirma en una línea — como el seam — no se abre en opciones.

La bitácora sale directo de ahí: opción recomendada elegida = `con_recomendada` · otra opción
= `corregidas` · respuesta libre = `propias`. Sin la herramienta, el formato es:

```
❓ **P1** — **<título de la pregunta>**: <cuerpo de la pregunta, puede ser varios párrafos,
incluidas opciones>

➡️ <tu respuesta recomendada>
```

Cada ronda de respuestas reforma el árbol: lo que quedó resuelto empuja la frontera hacia
afuera y desbloquea preguntas que dependían de ello. Una pregunta cuya respuesta depende de
otra abierta EN ESTA ronda pertenece a una ronda POSTERIOR, no a ésta.

**La sesión termina cuando la frontera está vacía**: cada rama del árbol visitada, nada asumido
en silencio. No colapses a spec hasta que el humano confirme que llegaron a entendimiento
compartido.

**Bitácora de rondas — se escribe al RECIBIR cada tanda de respuestas, no al final.** Una línea
por ronda en el archivo de trabajo de la corrida (`grill-rondas.jsonl`):

```json
{"ronda":1,"preguntas":6,"con_recomendada":4,"corregidas":1,"propias":1,"hechos_despachados":2}
```

`con_recomendada` = la respuesta fue la recomendada tal cual · `corregidas` = el humano
contradijo o modificó la recomendada · `propias` = respondió algo que ninguna opción traía ·
`hechos_despachados` = preguntas que NO llegaron al humano porque un subagente resolvió el
hecho. Reconstruirla al final es narración.

Para qué existe: **la compuerta se mide, no se supone.** La señal de alarma es rondas
contestadas 100% con la recomendada y 0 corregidas — un grill sano tiene correcciones del
humano. Si la señal aparece sostenida, el remedio es cambiar el formato de respuesta a PROSA
(sin opciones que tildar); esa decisión es del dueño del flujo, no del agente.

Cada pregunta anclada en el código REAL. **Buscar los HECHOS es tu trabajo, nunca del humano;
las DECISIONES son suyas.** Cuando una pregunta de la frontera necesita un hecho del entorno
—qué tabla, qué columna, qué usa hoy la pantalla equivalente, cómo se llama el servicio—
despachá un subagente a buscarlo; no le preguntes al humano nada que puedas averiguar vos.
**No te bloquees:** una exploración corriendo es un prerrequisito sin resolver, así que sólo
esperan las preguntas que cuelgan de ella. Y el subagente devuelve el hecho **con sus tres
anclas** (abajo), nunca un veredicto.

**El modelo del subagente se fija en el despacho, explícito — jamás se hereda.** El default de
sesión es configuración personal de quien corre, no del flujo. Los hechos deciden anclas y
descartan homónimos: una decisión degradada ahí es una spec degradada después.

**Y todo subagente despachado es un subagente COSECHADO.** Llevá la lista de los que
despachaste; integrá cada resultado a la ronda que lo esperaba. Un hecho despachado y nunca
leído es peor que no despacharlo.

Si la feature cruza a otro repo de la cadena, **leé el `CLAUDE.md` de ese repo antes de abrir
su código** (no se inyecta solo). Las respuestas del humano van fijando los 7 ejes + eje 0 EN
VIVO; cada decisión la resuelve él en el diálogo, no firmando un documento después.

**Antes de la primera pregunta, leé `docs/glosario.md`**: qué tabla es cada palabra del negocio
y qué homónimo NO es. Es un mapa, nunca una fuente — la ancla de DATOS sigue siendo obligatoria
aunque el término esté ahí. Cuando el grill resuelva uno nuevo, la fila entra al glosario en
ese mismo turno.

**Leer el glosario es la costumbre; modelar el dominio es el trabajo.** Durante toda la
entrevista, cuatro conductas activas — las cuatro con el mismo corte: **se afirma con
`archivo:línea` o con un `SELECT`, jamás con una impresión**:

- **Confrontar contra el glosario.** Si el humano usa un término que choca con lo escrito,
  decíselo en el momento.
- **Afilar lo difuso.** Un término que abarca dos cosas se parte ANTES de decidir sobre él.
- **Escenarios de borde.** Cuando se discute una relación del dominio, inventá el caso que la
  tensiona: la fila sin ninguno de los campos, la que aparece dos veces, la columna nullable
  cuyo getter promete no-null.
- **Contrastar contra el código — al humano también.** Si lo que se acaba de afirmar no es lo
  que hace el árbol, se dice, con la línea en la mano. Contradecir sin evidencia es ruido.

**Estándar de evidencia — las tres anclas.** Un referente (FK, catálogo, fuente de un select)
solo está «verificado» con las tres:

1. **DATOS** — query contra la tabla viva (conteo + muestra + fecha del último registro),
   pegada en la spec. Las conexiones salen de `tests/configuration.yaml`: **verificá que cada
   una apunte a DEV antes de la primera sonda**, y la sonda es un `SELECT`, jamás una
   escritura. Verificar una conexión y asumir las demás es la mitad de una guarda.
2. **USO** — consumidor actual señalado `archivo:línea`: ¿qué usa HOY la pantalla o el servicio
   equivalente para este mismo propósito? Y si la feature agrega un select, el USO incluye de
   qué se cura hoy el select equivalente — el catálogo entero casi nunca es la respuesta.
3. **DISCRIMINACIÓN** — todo homónimo o alternativa que un listado muestre se ABRE y se
   descarta con motivo escrito. El resultado se escribe en `docs/glosario.md`.

Leer la entity no es verificar: es leer la forma. Elegir por nombre o por existencia es
inferencia y se marca **DECISIÓN**.

**El seam decisorio NO es un menú — es una derivación que se confirma.** La estrategia ya la
decidió la plantilla (eje 6). Lo que cambia por feature es la sonda CONCRETA: qué endpoint, qué
valor. Derivala y presentala antes del colapso **con la pregunta del dev, no con la jerga del
artefacto**: el título es **«¿Cómo validamos que esta feature funciona?»** y el cuerpo va en
tres líneas — *qué se hace* · *qué tiene que devolver* (el valor que solo lo correcto produce)
· *qué queda manual y quién lo recorre*. Opciones: confirmar o corregir.

**Nada congela con pendientes**: cero `[confirmar]`/`[pendiente]` al colapsar.

**Lo que NO se grilla, primera familia: el PROCESO.** Cómo se empaqueta el trabajo — hogar de
la spec, ramas, orden de PRs — ya lo decidió la metodología. El test: si la respuesta vive en
un documento del flujo, es un HECHO — buscalo vos — no una decisión. El dev decide el DOMINIO
de su feature; el proceso lo decide el flujo.

**Lo que NO se grilla, segunda familia: las preguntas de alta fidelidad.** Si la pregunta es
*cómo se ve* o *cómo se siente*, no se conversa: se marca `ALTA FIDELIDAD`, se resuelve con un
artefacto (captura, mockup, prototipo) enlazado en la spec, y la entrevista sigue. Si se puede
decidir mirando lo REAL, no se fabrica nada. Lo que queda escrito es la DECISIÓN en texto, con
el adjunto como evidencia.

## Paso 3 — Colapso a spec

La spec colapsada se escribe en **`_local/corridas/<slug-de-la-premisa>/spec.md`** (git la
ignora). Es archivo de TRABAJO, no hogar: muere cuando el issue existe. **La plantilla
`docs/plantilla-7ejes.md` no se toca jamás: se copia su estructura.** Cuando el issue nazca,
la carpeta se renombra a `<repo>-<n>`.

Llenar la estructura DESDE la conversación, sin re-entrevistar. Todo hueco que el agente
rellene solo va marcado **DECISIÓN** — el completado silencioso es el modo de fallo por
defecto. El humano la mira UNA vez (ya la vivió).

## Paso 4 — Issue y rama (SOLO con OK explícito)

El hogar canónico de la spec es el ISSUE de GitHub: cuerpo = título + spec COMPLETA.
**Antes de publicar**: `bash tools/harness/spec-check.sh _local/corridas/<carpeta>/spec.md`.
**Después de publicar**: bajá el dump y compará —
`bash tools/harness/spec-dump.sh <owner/repo> <n>` y luego
`bash tools/harness/spec-check.sh <borrador> docs/specs/<n>/issue.md` — una sección que no
viaja convierte propuestas en hechos.

El issue se crea con `gh issue create` o con el MCP de GitHub que tengas. La rama:
`<n>-<slug-corto>`.

**Nada se publica (issue, rama) sin el OK del aprobador.** La compuerta humana pasa antes que
el issue exista.

Con el issue creado, la bitácora de rondas aterriza como `docs/specs/<n>/grill.json` (las
líneas de `grill-rondas.jsonl` + totales) y se commitea con la evidencia de la feature.

## Después — el flujo sigue solo

Con el issue publicado, ofrecé el siguiente paso en el mismo turno: `/implementar <n>`. De ahí
en adelante es UN solo flujo — dump → rojo → implementación → verde → triangular → recibo →
`/pr` — con la compuerta humana en cada frontera (publicar, pushear, DDL).
