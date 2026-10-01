> **Ejemplo.** Así se ve la descripción de un pull request que sale del flujo SDD (`/pr`).
> El caso, los nombres, los sha y las fechas son inventados; la forma es la que se usa.
> Corresponde al issue de ejemplo [`issue.md`](issue.md) y trae adentro su [`recibo.json`](recibo.json).

# #12 Buscar por nombre en Conductores asignados: nombre → códigos en el servicio, segunda forma de paginación

`Spinedac3` wants to merge 5 commits into `main` from `12-driver-search`

---

Closes #12 · padre Spinedac3/ddd-php#9 · pantalla web#29 (etiqueta: web#31)

## Qué cambia

Buscar «ana» en Catálogos → Envíos → Conductores asignados reventaba con `SQLSTATE[22018] nvarchar → int`: el repositorio comparaba el texto con `route_id`, `depot_id` y `driver_code`, y el nombre del conductor vive en el padrón, no en `driver_assignment`.

- **`DriverAssignmentService::listPaginated()`** (misma firma hacia el api): un texto no numérico se resuelve a códigos de conductor con `DriverRepository::listPaginated` (primer nombre, primer apellido, código) y viaja como `$codes` (D1).
- **`DriverAssignmentRepository`** pasa a la segunda forma de paginación: `listPaginatedDriverAssignment(..., array $codes = [])`; deja de extender `PaginatedListRepository` (D2). Con códigos filtra `driver_code IN (...)`; con texto y sin códigos devuelve página vacía sin consultar; un número sigue buscando por ruta, depósito o código (D3).
- Sin cambios de esquema, api ni web.

## Commits

| sha | mensaje |
|---|---|
| `e3b7a52` | 📝 docs(shipping): spec v1 of the drivers search by name, frozen in the issue |
| `f08c1d4` | 📝 docs(shipping): spec dump of the issue with its fetch stamp, the copy the receipt freezes |
| `a1f09e2` | ✅ test(shipping): the listing resolves a text search to driver codes and never compares text with integer columns — red before the change |
| `b7c2d10` | 🐛 fix(shipping): drivers search by name — the service resolves the text to driver codes and the repository pages with listPaginatedDriverAssignment(..., $codes) (D1-D3) |
| `c9e4f77` | ♻️ refactor(shipping): a text search with no resolved codes returns an empty page without querying |

## Spec y recibo

Spec v1 congelada en el issue (`docs/specs/12/issue.md`, `spec-check` 0). Rojo visto en `a1f09e2` (6 tests), verde en `c9e4f77`.

```json
{
  "recibo": "v0-piloto",
  "leeme": "APROBABLE = todo lo medido en verde · DEGRADADO = aprobable si las causas de 'notas' convencen (cada una dice si es del cambio o del kit) · INCONCLUSO = un verificador no pudo correr: no se sabe, se resuelve y se repite · FALLANDO = no aprobar. 'no_revisado' lista lo que este recibo NO mira y quien si lo cubre.",
  "issue": "12",
  "repo": "ddd-php",
  "rama": "12-driver-search",
  "fecha": "2026-05-12T10:42:15",
  "commit": "c9e4f77a1",
  "php": "8.3.14",
  "modelo": "claude-opus",
  "validacion_del_dev": null,
  "spec": {
    "sha256": "7c41d09b5e2f8a6634c1b7d20f9e53a8816d4c0e2b7f91a35d6c8e04f1b2e9a0",
    "cambiada": false
  },
  "fase_rojo": {
    "corridas": 1,
    "tests_vistos_fallar": [
      "…\\ORMDriverAssignmentRepositoryTest::testListPaginatedDriverAssignmentFiltersByCodes",
      "…\\ORMDriverAssignmentRepositoryTest::testListPaginatedDriverAssignmentNumericTextMatchesCode",
      "…\\ORMDriverAssignmentRepositoryTest::testListPaginatedDriverAssignmentWithTextDoesNotHitIntegerColumns",
      "…\\DriverAssignmentServiceTest::testListPaginatedComposesTheDriverOfEachRow",
      "…\\DriverAssignmentServiceTest::testListPaginatedPassesNumericTextStraight",
      "…\\DriverAssignmentServiceTest::testListPaginatedResolvesTextToDriverCodes"
    ]
  },
  "tests": {"corridos": 11, "fallas": 0, "nunca_rojos": []},
  "smoke": null,
  "gates": {
    "loads": {"exit": 0, "resumen": "loads: 3 archivos · 0 con error de sintaxis · 0 que no cargan"},
    "layers": {"exit": 0, "resumen": "layers: 3 archivos · 0 violacion(es)"},
    "schema": {"exit": 0, "resumen": "schema: no hay archivos de Persistencies/Entities que revisar."},
    "phpcs": {"exit": 0, "resumen": "phpcs: 5 archivos · 0 con violaciones"},
    "spec": {"exit": 0, "resumen": "spec-check: 0 violacion(es) en docs/specs/12/issue.md"}
  },
  "manifiesto": {"exit": 0, "estado": "VIGENTE — los verificadores son los mismos que se probaron el 2026-05-12T10:39:02 y todos cazaron lo que debian:"},
  "estado": "APROBABLE",
  "notas": [],
  "no_revisado": [
    "validacion del dev: sin lista — si la spec exige recorrido critico o CA manual, este recibo no dice que validar",
    "triangular (paso 8b): sin instrumento que lo verifique — el PR debe decir que caso se busco contra lo construido",
    "si el cambio hace lo que el negocio pidio: ningun gate lo mide — lo revisa el humano contra la spec",
    "la coherencia entre los PRs de la cadena (library/api/web): todavia no hay gate que la mire",
    "tests fuera de la lista de la spec: aca no corren — la suite completa la corre el pipeline del PR"
  ]
}
```

## Criterios de aceptación

- [x] CA-01 texto → asignaciones cuyo conductor tiene el texto en nombre o apellido (`testListPaginatedResolvesTextToDriverCodes`, `testListPaginatedDriverAssignmentFiltersByCodes`)
- [x] CA-02 número → ruta, depósito o código como hoy (`testListPaginatedPassesNumericTextStraight`, `testListPaginatedDriverAssignmentNumericTextMatchesCode`)
- [x] CA-03 texto sin conductor → página vacía, sin error del motor (`testListPaginatedDriverAssignmentWithTextDoesNotHitIntegerColumns`)
- [ ] CA-04 api/web sin cambios: validar en dev buscando «rivas» en la pantalla (recorrido del dev)

## Después del merge

Release library **1.4.1** → api **2.3.1** (sólo re-amarrar la dependencia) → despliegue del api, sin reiniciar servicios.

## Retroalimentación al método

F1 en el #9: el buscador del listado nunca se especificó y ningún test lo ejercitó con texto. Regla: toda spec de listado declara sus campos de búsqueda; todo `$searchFieldsDirect` sobre columnas enteras lleva un test de integración con texto.

---

✓ **All checks have passed** — lint · phpcs · architecture (loads · layers · schema) · tests
