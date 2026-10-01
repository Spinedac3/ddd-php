# Glosario del dominio — qué tabla es cada palabra, y qué homónimo NO es

`/grill` lo lee antes de la primera pregunta y le agrega una fila cada vez que resuelve un
homónimo. Es un **mapa, no una fuente**: la ancla de DATOS (query viva) sigue siendo
obligatoria aunque el término esté acá; lo que ahorra es llegar sabiendo qué homónimo abrir.

Cada fila dice qué ES la palabra (tabla, columna y conexión exactas), qué **NO es** (el
homónimo que ya confundió a alguien) y la evidencia que lo sostiene. Las cifras medidas no van
acá: caducan — van en la spec del issue que las midió.

La base no trae dominio propio, así que las filas de abajo son un **ejemplo inventado** de la
forma. Reemplazalas por las de tu proyecto.

| palabra del negocio | qué ES | qué NO es | evidencia |
|---|---|---|---|
| **conductor** | `hr_driver` (conexión `hr`): el padrón de personas habilitadas para manejar | `driver_assignment`: esa tabla es la *asignación* de un conductor a una ruta, no el conductor | `src/Entities/Shipping/Driver.php` · D1 del issue de ejemplo |
| **nombre del conductor** | `hr_driver.first_name` + `last_name` — vive en el padrón | no hay columna de nombre en `driver_assignment`: buscar por nombre ahí compara texto con códigos | [`docs/specs/12/issue.md`](specs/12/issue.md), eje 1 |
| **ruta** | `route` (conexión `shipping`): el recorrido planificado | el *viaje* de un día concreto, que es otra tabla | — |

## Reglas que salieron de resolver homónimos

- Una palabra que abarca dos tablas se parte ANTES de decidir sobre ella.
- El nombre de la clase no es evidencia: se elige por el consumidor actual (`archivo:línea`),
  no por cómo se llama.
