---
description: Genera y publica la descripción de un pull request desde el diff real y el recibo
---

# /pr — Generador de descripción de pull request

Genera un borrador completo leyendo el diff real y solo deja abierto lo no-inferible.

## Flujo

### 0. Identificar el PR
**Si venís de `/implementar`: repo, rama y número del issue ya son conocidos — no se los
preguntes al usuario, y el recibo está en `docs/specs/<n>/recibo.json`.** Si el PR todavía no
existe, se CREA (`gh pr create` o el MCP de GitHub que tengas) con la descripción generada; si
existe, se actualiza (`gh pr edit`).

### 1. Leer el diff real
- `git diff <base>...HEAD --stat` para ver tamaño por archivo.
- **Leé los archivos clave** (no solo nombres): entity principal, service principal, value
  objects nuevos, y el diff de los archivos modificados de más de 30 líneas. De los tests basta
  el nombre.

### 2. Generar el borrador completo

**Todo lo siguiente se infiere del diff real — no se pregunta al usuario:**

- **Tipo**: `feature`, `fix`, `refactor`, `docs`, `chore`.
- **Objetivo**: del título + nombres de entidades + campos clave. 2-3 líneas.
- **Cambios por capa**: Contract → Entity → Persistency → Repository → Service → Factory →
  Aggregate → Exception → Test. Para cada archivo, el rol + el detalle clave extraído del diff.
- **Decisiones**: patrones no-obvios del código, cada una con «qué se eligió + por qué».
- **Cierre**: `Closes #N` SOLO en el repo dueño del issue. Los demás repos de la cadena no
  cierran nada: citan al hogar con ruta completa, `Implementa owner/repo#N`.
- **Breaking changes**: cambios en firmas públicas, constructores, métodos eliminados.

### 3. Testing y evidencia: salen de la sesión, no del usuario

Todo lo que el usuario contestaría ya pasó delante tuyo en esta sesión o está en el recibo;
preguntarlo es re-preguntar lo que sabés.

- **Tests**: cuántos, vistos en rojo en qué commit y en verde en cuál (el recibo trae
  `fase_rojo.tests_vistos_fallar`). Nunca «tests incluidos» a secas.
- **Gates**: los que corriste y su resultado. Si uno no corrió, decilo como no corrido.
- **Reproducciones y mediciones**: se pegan con sus números.
- **Validación del dev**: la ÚNICA casilla que queda `[ ]` — lo que el dev recorre a mano. Si
  el recibo trae `validacion_del_dev`, se copia.
- **Sin spec** (bug de un archivo, fuera de `/implementar`): la sección Recibo lo dice en una
  línea y apunta al issue; no se inventa un JSON.

### 4. Publicar

- Si el usuario ya dijo **«push y PR»**, **«procede»** o equivalente en este turno o el
  anterior: crear o actualizar el PR directamente y devolver la URL con un resumen de 3 líneas.
- Si `/pr` llegó solo: mostrar la descripción completa y una sola pregunta: «¿Publico?».

### 5. Verificar
`gh pr view --json url,title` → devuelve URL y título.

## Plantilla de salida

Las secciones marcadas *(spec)* van solo cuando la pieza viene de `/implementar`. Un ejemplo
completo: el [pull request #4](https://github.com/Spinedac3/ddd-php/pull/4) de este repositorio.

```markdown
**Tipo:** <feature | fix | refactor | docs | chore>

## Objetivo
<qué hace, con las D-N que materializa. En un fix: qué hacía mal, con el caso real>

## Causa, medida antes del código          ← fix
<mecanismo, archivo por archivo; antes/después de la reproducción en DEV>

## Retroalimentación al método (SDD)          ← (spec)
<qué sostuvieron los gates solos, escapes y la regla que cada escape dejó>

## Cambios por capa
<Contract → Entity → Persistency → Repository → Service → Factory → Aggregate → Exception → Test>

## Lo que NO se toca (eje 2, literal)                          ← (spec)

## Decisiones
- **<qué se eligió>:** <por qué>. Incluye lo dejado a propósito (deuda declarada).

## Testing
- [x] <N> tests vistos en rojo en `<sha>` y en verde en `<sha>`
- [x] Gates: <lista con resultado>
- [x] <reproducciones, pruebas punta a punta — con números>
- [ ] Validación del dev: <lo que recorre a mano>

| CA | Cubierto por |                                          ← (spec)
<un test con nombre por criterio; «manual» donde no hay test>

## Breaking changes
<firmas afectadas y quién las construye; si no hay: «Ninguno»>

## Recibo — <ESTADO> (commiteado en `docs/specs/N/recibo.json`; certifica `<sha>`)
```json
<recibo verbatim>
```

---
Closes #<n>
```

## Reglas

- **Leé el diff real**, no solo nombres de archivo.
- **No le pidas al usuario lo que ya está en el diff ni lo que pasó en la sesión.** Lo único
  que queda abierto es la validación a mano del dev, y va como casilla pendiente.
- **Una orden previa de «push y PR» / «procede» es la confirmación.** Sin ella, una sola
  pregunta: «¿Publico?».
- **Si el dev corrige el flujo en el chat, la corrección se escribe en este archivo en el mismo
  turno.** Aplicarla solo al PR de hoy es no aplicarla.
- Español, conciso.
