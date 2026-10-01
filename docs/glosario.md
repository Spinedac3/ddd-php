# Glosario del dominio — qué tabla es cada palabra, y qué homónimo NO es

`/grill` lo lee antes de la primera pregunta y le agrega una fila cada vez que resuelve un
homónimo. Es un **mapa, no una fuente**: la ancla de DATOS (query viva) sigue siendo
obligatoria aunque el término esté acá; lo que ahorra es llegar sabiendo qué homónimo abrir.

Cada fila dice qué ES la palabra (tabla, columna y conexión exactas), qué **NO es** (el
homónimo que ya confundió a alguien) y la evidencia que lo sostiene. Las cifras medidas no van
acá: caducan — van en la spec del issue que las midió.

Las filas de abajo salen del dominio de muestra `Shipping`. Reemplazalas por las de tu
proyecto.

| palabra del negocio | qué ES | qué NO es | evidencia |
|---|---|---|---|
| **conductor** | `driver` (la tabla del modelo `Persistencies/Eloquent/Shipping/Driver`): la persona habilitada para manejar | la *asignación* de un conductor a una ruta, que sería otra tabla | `src/Entities/Shipping/Driver.php` |
| **código del conductor** | `driver.code` — obligatorio, el identificador de negocio | la llave: la identidad de la tabla es `driver.id` | decisión D1 del [issue #3](https://github.com/Spinedac3/ddd-php/issues/3) |

## Reglas que salieron de resolver homónimos

- Una palabra que abarca dos tablas se parte ANTES de decidir sobre ella.
- El nombre de la clase no es evidencia: se elige por el consumidor actual (`archivo:línea`),
  no por cómo se llama.
