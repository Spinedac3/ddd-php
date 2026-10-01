---
proyecto: Spinedac3/ddd-php
iid: 3
titulo: Dominio de muestra: conductores (Shipping/Driver)
estado: abierto
actualizado_en_github: 2026-10-01T19:31:29Z
fetched_at: 2026-10-01T13:31:36
---

# Spec de feature — «Dominio de muestra: conductores (Shipping/Driver)»

estado: BORRADOR | APROBADA por ___ el ___
(el sha256 lo congela `recibo.sh rojo`; cambiarla después exige causa escrita en el PR)

**Origen:** la base no trae ningún dominio, así que `CLAUDE.md` y el agente `ddd-backend`
enseñan cada pieza con un ejemplo que no existe en el árbol. Este issue agrega un dominio
mínimo y real — un conductor — para que cada pieza tenga un ejemplar que carga, pasa los gates
y se puede copiar. Es también la primera corrida del flujo SDD sobre este repositorio.

**Acta:** sin entrevista. El dueño del repositorio delegó este ejemplo de documentación, así
que las decisiones de abajo las tomó el agente y van marcadas **DECISIÓN** para que se vean; la
compuerta queda sin tildar hasta que él la revise en el pull request.

---

## Eje 0 — Mapa de impacto de la cadena (cross-repo)

| pieza | ¿tocada? | qué cambia / por qué NO se toca |
|---|---|---|
| **library** (este repo) | **sí** | dominio nuevo `Shipping`: entity, persistency, contrato, repositorio ORM, factory, service y excepción de `Driver` |

La cadena de este repositorio es una sola pieza: no hay api ni frontend que lo consuman.

### Issues, ramas y orden de PRs

| pieza | repo e issue | rama | rol |
|---|---|---|---|
| library | `Spinedac3/ddd-php` (este issue) | `3-shipping-driver` | única pieza, hogar de la spec |

**Orden:** un solo PR hacia `main`.

---

## 1. Esquema + migración + backfill

Sin base de datos: este repositorio no trae conexiones vivas ni migraciones, así que **no hay
query contra una tabla que pegar** y las tres anclas no aplican a ningún referente.

- **DECISIÓN D1** — la tabla que el modelo declara es `driver`, con `id` como llave y las
  columnas `code` (NOT NULL) y `first_name`, `last_name` (nullable), más las de auditoría y
  borrado lógico que traen los traits (`created_at/by`, `updated_at/by`, `deleted_at/by`).
- migración: no aplica porque el repositorio no ejecuta DDL.
- backfill: no aplica porque no hay datos.

---

## 2. Alcance — y qué NO se toca

- **Entra:** las siete piezas de `Driver` en el dominio `Shipping` y sus tests unitarios.
- **Entra:** apuntar `CLAUDE.md` a estos archivos como ejemplares, ahora que existen.
- **NO se toca:** las clases base (`AbstractEntity`, `ORMAbstractRepository`, los contratos);
  los gates y el kit del recibo; ningún otro dominio.
- **NO entra** (D2): paginación, búsqueda, `create`/`update`/`delete`. Es un ejemplar de
  lectura por llave; lo demás se agrega cuando una feature lo pida.

---

## 3. Servicio dueño

- servicio: `src/Services/Shipping/DriverService.php` — nuevo. Se llama por la cosa que posee:
  la entity `src/Entities/Shipping/Driver.php`.
- transacción: no hay escritura.
- rechazos:

  | qué se rechaza | clase | código |
  |---|---|---|
  | identificador no positivo | `InvalidArgumentException` | 406 |
  | el conductor no existe | `DriverNotFoundException extends NotFoundException` | 404 |

---

## 4. Contrato: nuevo vs reuso

- contrato: `src/Contracts/Repositories/Shipping/DriverRepository.php` — nuevo.
- aridad de clave: `AbstractRepositorySingleKey`, porque la identidad de la tabla es `id` (D1).
- behaviors compuestos: ninguno — no se implementa ninguna escritura (D2).
- paginación: no aplica (D2).
- métodos: `get(mixed $key): Driver` (heredado, estrecha el retorno) y
  `findById(int $id): ?Driver`.

---

## 5. Retorno

- entity nueva: `src/Entities/Shipping/Driver.php`.
- `findById()` devuelve `?Driver` — puede volver vacío.
- `get()` y `DriverService::get()` devuelven `Driver` y controlan el error: lanzan
  `DriverNotFoundException extends NotFoundException`.
- ¿listado con datos de otra tabla? no — no hay listado (D2).

---

## 6. Tests exigidos

> Seam decisorio: `DriverService::get(7)` sobre un repositorio cuyo modelo devuelve la fila `{id: 7, code: 'D-3107'}` ⇒ `getCode() === 'D-3107'` — el valor recorre modelo → repositorio → entity → servicio; sin base de datos, es el punto más alto que este repositorio permite observar.

- `tests/Unit/Entities/Shipping/DriverTest.php`
  - [ ] `testHydratesTheColumns` — asserta: `getId() === 7`, `getCode() === 'D-3107'`
  - [ ] `testRequiresTheCode` — asserta: sin `code` lanza `UnderflowException`
- `tests/Unit/Repositories/Database/ORM/Shipping/ORMDriverRepositoryTest.php`
  - [ ] `testFindByIdRejectsANonPositiveId` — asserta: `InvalidArgumentException` con código 406
  - [ ] `testFindByIdReturnsNullWhenMissing` — asserta: `null`
  - [ ] `testFindByIdHydratesTheDriver` — asserta: `getCode() === 'D-3107'`
  - [ ] `testGetThrowsWhenMissing` — asserta: la excepción de conductor no encontrado, código 404
- `tests/Unit/Services/Shipping/DriverServiceTest.php`
  - [ ] `testGetReturnsTheDriverOfTheRepository` — asserta: `getCode() === 'D-3107'` (el seam)
- `tests/Unit/Factories/Repositories/Shipping/DriverFactoryTest.php`
  - [ ] `testGetReturnsTheSameRepository` — asserta: dos llamadas a `get()` devuelven la misma instancia

---

## 7. Criterios de aceptación verificables

- CA-01: CUANDO se pide el conductor 7 y existe con código `D-3107`, ENTONCES el servicio
  devuelve una entity cuyo `getCode()` es `D-3107`. [6: testGetReturnsTheDriverOfTheRepository]
- CA-02: CUANDO se pide un conductor que no existe, ENTONCES `findById()` devuelve `null` y
  `get()` lanza la excepción con código 404. [6: testFindByIdReturnsNullWhenMissing, testGetThrowsWhenMissing]
- CA-03: CUANDO el identificador es cero o negativo, ENTONCES se rechaza con código 406 antes
  de consultar. [6: testFindByIdRejectsANonPositiveId]
- CA-04: CUANDO a la fila le falta `code`, ENTONCES la entity no se construye. [6: testRequiresTheCode]
- CA-05: las siete clases cargan y pasan `loads.sh`, `layers.sh` y `phpcs.sh`. [recibo]

---

## Mapa de decisiones

**Duraderas: D1**

- D1 DURADERA — la identidad de `Driver` es `id` y `code` es obligatorio · revertir=cambia el contrato y la llave de quien lo consuma ✓ · sorprende=el código de negocio no es la llave ✓ · perdedora=usar `code` como llave ✓
- D2 — ejemplar de solo lectura por llave · sin marca: se amplía sin romper nada · evidencia: eje 2
- D3 — el seam es unitario, con el modelo simulado · sin marca: el repositorio no trae base de datos · evidencia: eje 6

**FUERA DE ALCANCE:** un segundo dominio, el listado paginado y el CRUD.

---

## Retroalimentación al método (SDD)

- **F1 · una spec sin entrevista.** No hubo `/grill`: el dueño delegó el ejemplo. Cómo se
  cubrió: toda decisión va marcada y la compuerta queda abierta para el pull request. Estado:
  abierto — el flujo no dice qué hacer cuando la entrevista se delega.

---

## Riesgo

- **eje de mayor riesgo: 4 (contrato)** — es el que los próximos dominios van a copiar.
- riesgo del cambio: 0 de 4 — código nuevo, sin datos ni consumidores.

## Compuerta (llena el humano, a mano)

- [ ] eje de mayor riesgo confirmado o corregido
- [ ] **eje 0 leído fila por fila**
- [ ] D1..D3 confirmadas o corregidas
- [ ] «NO se toca» del eje 2 leído y completo
- aprobada por: ___ · fecha: ___ · minutos que tomó: ___

