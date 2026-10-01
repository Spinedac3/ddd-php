---
proyecto: Spinedac3/ddd-php
iid: 12
titulo: "Buscar por nombre en Conductores asignados revienta: la búsqueda compara texto con columnas enteras"
estado: abierto
actualizado_en_github: 2026-05-12T16:20:41Z
fetched_at: 2026-05-12T10:31:09
---

> **Ejemplo.** Este archivo muestra cómo se ve el dump congelado de una spec real del flujo SDD.
> El caso, los nombres de clases y tablas, los códigos y las fechas son inventados; la forma
> —los ocho ejes, el mapa de decisiones, la compuerta— es exactamente la que se usa.

# Spec de bug — issue #12 «Buscar por nombre en Conductores asignados revienta»

estado: BORRADOR | APROBADA por ___ el ___
(el sha256 lo congela `recibo.sh rojo`; cambiarla después exige causa escrita en el PR)

**Origen:** visto en producción el 2026-05-12 (web 3.2.0 / library 1.4.0), pantalla Catálogos →
Envíos → Conductores asignados, al buscar «ana»:
`SQLSTATE[22018] Conversion failed when converting the nvarchar value 'ana' to data type int`
sobre `driver_assignment.route_id = ana or depot_id = ana or driver_code = ana`.
**Acta:** la sesión de grill con Spinedac3, 2026-05-12 (decisiones D1..D3 abajo). Padre: `Spinedac3/ddd-php#9`.

---

## Eje 0 — Mapa de impacto de la cadena (cross-repo)

| pieza | ¿tocada? | qué cambia / por qué NO se toca |
|---|---|---|
| **library** (este repo) | **sí** | `DriverAssignmentService::listPaginated()` resuelve el texto a códigos de conductor por nombre; `DriverAssignmentRepository` pasa a la segunda forma de paginación con `$codes` |
| **api** | no | ya manda el texto en `FiltersInfo` y llama al servicio con la misma firma; nada que cambiar en su módulo `Shipping/DriverAssignment/` |
| **web** | no | la pantalla ya envía el texto del buscador; la etiqueta «Todas las rutas» es otro bug, `web#31` |
| **app móvil** | no | no consume este listado |
| **notificaciones** | no | sin eventos nuevos |

### Issues, ramas y orden de PRs

| pieza | proyecto e issue | rama | rol |
|---|---|---|---|
| library | `Spinedac3/ddd-php#12` | `12-driver-search` | única pieza |

**Orden:** library → release 1.4.1 → api re-amarra la dependencia (2.3.1, sin código) → despliegue.

---

## 1. Esquema + migración + backfill

Sin cambios de esquema, sin migración, sin backfill. El nombre del conductor vive en el padrón
(`hr_driver`, conexión `hr`), no en `driver_assignment` (conexión `shipping`): son bases distintas
y no hay JOIN.

---

## 2. Alcance — y qué NO se toca

- **Se busca por primer nombre y primer apellido del conductor, o por su código** (D1): es lo que
  `ORMDriverRepository::listPaginated()` ya indexa (`$searchFieldsLike = code, first_name,
  last_name`). «Rivas» encuentra a Ana Rivas Soto; «Soto» no.
- **Texto numérico busca como hoy** por `route_id`, `depot_id` o `driver_code` (D3).
- **NO se toca:** `DriverRepository` (ningún método nuevo); el api; la web; la búsqueda
  por nombre de ruta o de depósito (fuera de alcance: sería otro paso del mismo tipo contra
  `Route`, y nadie lo pidió).

---

## 3. Servicios dueños

`DriverAssignmentService` (existente, dueño de `DriverAssignment`). Su `listPaginated()` conserva
la firma hacia el api y gana un paso previo: si el texto de `FiltersInfo` no es numérico, pide a
`DriverRepository::listPaginated('code', 100, 1, false, $filters)` y saca el código de cada
conductor con el getter que la entity `Driver` ya tiene; los pasa al repositorio como `$codes`.
Con texto numérico o vacío, `$codes` va vacío. Sin transacción: es una lectura.

---

## 4. Contrato: nuevo vs reuso

`src/Contracts/Repositories/Shipping/DriverAssignmentRepository.php` deja de extender
`PaginatedListRepository` y declara la segunda forma (CLAUDE.md § Paginación):

```php
public function listPaginatedDriverAssignment(
    string $orderBy,
    int $perPage,
    int $page = 1,
    bool $reversed = false,
    ?FiltersInfo $filters = null,
    array $codes = []
): PaginatedEntityCollection;
```

`ORMDriverAssignmentRepository::listPaginatedDriverAssignment()`: mismo cuerpo que el
`listPaginated()` de hoy con dos diferencias: con `$codes` no vacío agrega
`whereIn('driver_code', $codes)`; y la búsqueda directa sobre las tres columnas enteras sólo se
aplica cuando el texto es numérico (`ctype_digit`). Texto no numérico y `$codes` vacío → página
vacía, nunca un `=` contra un `int`.
Guard: `$codes` con un valor no entero → `InvalidArgumentException` 406.

---

## 5. Retorno

Sin cambio: `AggregateCollectionWithPaginatedListingAggregate` con filas
`DriverAssignmentWithDriverAggregate`.

---

## 6. Tests exigidos

- `tests/Unit/Services/Shipping/DriverAssignmentServiceTest.php`
  - [ ] `testListPaginatedComposesTheDriverOfEachRow` — pasa a esperar `listPaginatedDriverAssignment(..., [])`
  - [ ] `testListPaginatedResolvesTextToDriverCodes` — «ana» → consulta conductores → `[3107, 4410]`
  - [ ] `testListPaginatedPassesNumericTextStraight` — «3107» → no consulta conductores → `[]`
- `tests/Integration/Repositories/Database/ORM/Shipping/ORMDriverAssignmentRepositoryTest.php`
  - [ ] `testListPaginatedDriverAssignmentFiltersByCodes` — con `[code]` devuelve sólo esas filas
  - [ ] `testListPaginatedDriverAssignmentNumericTextMatchesCode` — «3107» encuentra la fila por su código
  - [ ] `testListPaginatedDriverAssignmentWithTextDoesNotHitIntegerColumns` — texto → página vacía, sin excepción SQL (el que hoy revienta)

**Seam decisorio:** buscar «ana» con un `DriverRepository` que devuelve la conductora **3107**
debe llamar al repositorio con `$codes = [3107]`; la lista de códigos es el valor que sólo el
padrón produce, el texto nunca llega al repositorio de asignaciones.

---

## 7. Criterios de aceptación verificables

- CA-01: CUANDO el buscador recibe texto, ENTONCES el listado trae las asignaciones cuyo conductor
  tiene ese texto en el primer nombre o el primer apellido. [6.library.2, 6.library.4]
- CA-02: CUANDO el buscador recibe un número, ENTONCES busca por ruta, depósito o código como hoy. [6.library.3, 6.library.5]
- CA-03: CUANDO el texto no coincide con ningún conductor, ENTONCES devuelve página vacía y ningún
  error del motor de base de datos. [6.library.6]
- CA-04: api y web no cambian: la misma consulta de hoy funciona.

---

## Mapa de decisiones

**Duraderas: D1 · D2 · D3**

- D1 DURADERA (Spinedac3, 2026-05-12) — buscar es por nombre del conductor, resuelto en el padrón vía
  `DriverRepository::listPaginated` · revertir=quitar el paso previo del servicio ✓ ·
  sorprende=el nombre no está en la tabla del listado ✓ · perdedora=buscar sólo por códigos ✓
- D2 DURADERA — segunda forma de paginación, `$codes` como parámetro propio, nunca dentro de
  `FiltersInfo` (CLAUDE.md) · revertir=una firma ✓ · sorprende=obliga a soltar
  `PaginatedListRepository` ✓ · perdedora=repositorio que importa la factory de conductores ✓
- D3 DURADERA — texto numérico conserva la búsqueda directa por ruta, depósito y código ·
  revertir=un `ctype_digit` ✓ · sorprende=nadie la usa aún, pero ya existía ✓ · perdedora=quitar
  la búsqueda directa ✓

---

## Retroalimentación al método (SDD)

- **F1 · el buscador del listado no se especificó en el #9.** La spec del padre fijó contrato,
  retorno y tests del listado, pero no qué significa «buscar»; el repositorio heredó
  `$searchFieldsDirect` sobre enteros y ningún test lo ejercitó con texto, así que gates y recibo
  pasaron en verde. Regla que sale: toda spec de listado declara sus campos de búsqueda, y todo
  `$searchFieldsDirect` sobre columnas enteras lleva un test de integración con texto.

---

## Riesgo

- **eje de mayor riesgo: 4 (contrato)** — soltar `PaginatedListRepository` y renombrar el método:
  si algo más llamara a `listPaginated()` del repositorio, fatal al cargar. Medido: el único
  llamador es `DriverAssignmentService` (`loads.sh` lo confirma).
- **riesgo del cambio: 1 de 4** — lectura, un servicio, sin esquema ni api.

---

## Compuerta (llena el humano, a mano)

- [ ] eje de mayor riesgo confirmado o corregido
- [ ] **eje 0 leído fila por fila**
- [ ] D1..D3 confirmadas
- aprobada por: ___ · fecha: ___ · minutos que tomó: ___
