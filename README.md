# DDD para PHP

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Spinedac3/ddd-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/Spinedac3/ddd-php/?branch=main)

> **In English:** this repository holds the base architectural classes for a layered,
> Domain-Driven PHP project, plus the Spec-Driven Development flow used to build on top of them
> with coding agents. The code, its comments and [`CLAUDE.md`](CLAUDE.md) are in English; the
> documentation of the flow (this README, the commands, the spec template and the examples) is
> in Spanish.

## ¿Qué es esto?

Este repositorio busca implementar las clases de arquitectura para cualquier proyecto que
quiera basarse en el [Domain Driven Design de Eric Evans](https://www.amazon.com/gp/product/0321125215/ref=as_li_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=0321125215&linkCode=as2&tag=martinfowlerc-20).

## Cómo está organizado

El proyecto se organiza en directorios principales, uno por cada objeto base de DDD:

- Aggregates
- Builders
- Entities
- Factories
- Repositories
- Services
- ValueObjects

Y además, algunos objetos de infraestructura:

- Errors
- Contracts (principalmente para los repositorios)
- Traits (propiedades compartidas de las entidades)

## El flujo SDD

Este repositorio también trae el **flujo** con el que se construye sobre estas clases usando
agentes de código: Spec-Driven Development. La spec se aprueba antes de escribir código, los
tests se ven fallar antes de que el código exista, y cada pull request lleva la evidencia de lo
que de verdad se corrió.

**[El flujo, de una mirada →](https://spinedac3.github.io/ddd-php/)** — diez piezas en tres
etapas, cada una con un ejemplo de cómo se ve en GitHub.

```
decidir       premisa → /grill → spec de 7 ejes → issue
construir     fase roja → código → verde + gates → triangular      (lo corre /implementar)
entregar      recibo → /pr
```

Hay un ejemplo completo y llenado en [`docs/specs/12/`](docs/specs/12/): la
[spec](docs/specs/12/issue.md), la [bitácora de la entrevista](docs/specs/12/grill.json), el
[recibo](docs/specs/12/recibo.json) y el [pull request](docs/specs/12/pr.md). El caso y sus
datos son inventados; la forma es exactamente la que produce el flujo.

### Conceptos

| Término | Qué significa acá |
|---|---|
| **Harness** (arnés) | Todo lo que rodea al agente para convertir autonomía cruda en trabajo dirigido: comandos, gates, plantillas, verificadores. El modelo es el motor; el harness es el chasis, los frenos y el tablero. Acá es `tools/` más `.claude/`. |
| **SDD** — Spec-Driven Development | La spec manda; el chat es desechable. Antes de una línea de código, el pedido se convierte en una especificación revisada por un humano. Lo que el agente rellene solo se marca `DECISIÓN` para que lo veas. |
| **RDD** — Receipt-Driven Development | La revisión no es una opinión: es un recibo atado a una huella — el sha de la spec, qué tests se vieron fallar y pasar, qué gates corrieron. Si cambia un byte, el recibo se invalida. |
| **Gate** (compuerta) | Un verificador que corre solo: ¿la clase carga? ¿quién importa a quién? Regla de la casa: un check que devuelve cero no vale hasta verlo devolver uno. |
| **Spec (de 7 ejes)** | El artefacto central: un formulario fijo de ocho preguntas — impacto en la cadena, esquema, alcance, servicio dueño, contrato, retorno, tests y criterios de aceptación. Vive en el issue y se congela con un sha al arrancar los tests. |
| **Recibo** | El documento que `/implementar` deja al cerrar. Lo emite el instrumento (`recibo.sh`), no la narración del agente, y sale con un estado: `APROBABLE` · `DEGRADADO` · `INCONCLUSO` · `FALLANDO`. Viaja dentro del pull request. |
| **Seam** (costura) | El punto único y decisorio donde la feature toca la realidad y se valida por fuera: el endpoint que responde, la query contra la base viva. Exige un valor que solo el referente correcto produce — no que «algo aparezca». |
| **Triangular** | Con el verde en la mano, buscar a propósito un caso que rompa lo *construido*. Los tests de la spec interrogan lo prometido; éste, lo que de verdad se escribió. |
| **Mutante** | Un defecto sembrado a propósito para ver si los chequeos lo cazan. Si sobrevive con todo en verde, el chequeo no vigila nada. Cada gate de este repo recibió un archivo malo a propósito antes de confiarle algo. |
| **Tres anclas** | Un referente (llave foránea, catálogo, fuente de un select) solo está verificado con las tres: **datos** (una query contra la tabla viva), **uso** (el consumidor actual, `archivo:línea`) y **discriminación** (cada homónimo abierto y descartado por escrito). |

### Qué trae

| Ruta | Qué es |
|---|---|
| [`CLAUDE.md`](CLAUDE.md) | La doctrina: las piezas, las reglas de dirección, las trampas, el estilo. Se carga en cada sesión. |
| [`.claude/agents/ddd-backend.md`](.claude/agents/ddd-backend.md) | El agente que implementa: la plantilla exacta de cada pieza. |
| [`.claude/commands/grill.md`](.claude/commands/grill.md) | `/grill` — la entrevista que convierte una premisa en spec e issue. |
| [`.claude/commands/implementar.md`](.claude/commands/implementar.md) | `/implementar` — rojo, código, verde, triangular, recibo. |
| [`.claude/commands/pr.md`](.claude/commands/pr.md) | `/pr` — la descripción del pull request, armada desde el diff real y el recibo. |
| [`docs/plantilla-7ejes.md`](docs/plantilla-7ejes.md) | La plantilla de la spec. |
| [`docs/glosario.md`](docs/glosario.md) | El glosario del dominio: qué tabla es cada palabra, y qué homónimo no es. |
| `tools/architecture/` | Los gates: `loads.sh` (cada clase se declara), `layers.sh` (quién importa a quién, nombres, reglas de servicio), `phpcs.sh` (PSR-12). |
| `tools/harness/` | El kit del recibo: `recibo.sh`, `spec-check.sh`, `spec-dump.sh` y el hook `commit-msg.sh`. |

### Qué necesitás

- PHP ^8.3 y `composer install` — los gates necesitan el autoloader y `phpcs`.
- [Claude Code](https://claude.com/claude-code), que toma solo el `CLAUDE.md`, el agente y los
  comandos de este repositorio.
- Opcional: la CLI [`gh`](https://cli.github.com/) o un servidor MCP de GitHub, para crear
  issues y pull requests sin salir de la sesión. `spec-dump.sh` lee issues públicos sin token;
  los repositorios privados necesitan `GITHUB_TOKEN`.

La entrevista de `/grill` parte de las skills de grilling de Matt Pocock, que vale la pena
instalar por sí solas:
[`grill-me`](https://github.com/mattpocock/skills/tree/main/skills/productivity/grill-me),
[`grill-with-docs`](https://github.com/mattpocock/skills/tree/main/skills/engineering/grill-with-docs)
y [`tdd`](https://github.com/mattpocock/skills/tree/main/skills/engineering/tdd), de
[mattpocock/skills](https://github.com/mattpocock/skills). Sobre esa idea, `/grill` agrega las
tres anclas, la bitácora medida de la entrevista y el colapso a la spec de 7 ejes.

### Cómo se corre

```bash
composer install
printf '#!/usr/bin/env bash\nexec bash "$(git rev-parse --show-toplevel)/tools/harness/commit-msg.sh" "$@"\n' > .git/hooks/commit-msg

# los gates, sobre lo que cambiaste contra la rama base
bash tools/architecture/loads.sh
bash tools/architecture/layers.sh
bash tools/architecture/phpcs.sh
```

Después, en una sesión de Claude Code: `/grill <el pedido>`, aprobás la spec, y
`/implementar <número del issue>`.

Para adoptar el flujo en un proyecto propio: copiá `.claude/`, `tools/`,
`docs/plantilla-7ejes.md` y `docs/glosario.md`, declará los repositorios de tu cadena en
`tools/harness/cadena.txt` (uno por línea), y reescribí `CLAUDE.md` contra tu propio árbol —
las reglas que importan son las que mediste sobre tu código.

## Licencia

Distribuido bajo la licencia GPL v.2. Ver [LICENSE](LICENSE) para más información.
