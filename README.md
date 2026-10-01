# DDD for PHP

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Spinedac3/ddd-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/Spinedac3/ddd-php/?branch=main)

## What is this?

This repository intends to implement the architectural classes for any project that wants to base on [Eric Evans' Domain Driven Design](https://www.amazon.com/gp/product/0321125215/ref=as_li_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=0321125215&linkCode=as2&tag=martinfowlerc-20).

## How is this organized

This project is organized in main directories, considering each of the base objects of DDD:

- Aggregates
- Builders
- Entities
- Factories
- Repositories
- Services
- ValueObjects

And additionally, some infrastructural objects:

- Errors
- Contracts (mainly for repositories)
- Traits (shared properties of the entities)

## The SDD flow

This repository also ships the **flow** used to build on top of these classes with coding
agents: Spec-Driven Development. The spec is approved before any code is written, the tests are
seen failing before the code exists, and every pull request carries the evidence of what was
actually run.

**[See the flow at a glance →](https://spinedac3.github.io/ddd-php/)** — ten pieces in three
stages, each with an example of how it looks on GitHub.

```
decide        premise → /grill → 7-axis spec → issue
build         red phase → code → green + gates → triangulate      (run by /implementar)
deliver       receipt → /pr
```

A complete, filled-in example lives in [`docs/specs/12/`](docs/specs/12/): the
[spec](docs/specs/12/issue.md), the [interview log](docs/specs/12/grill.json), the
[receipt](docs/specs/12/recibo.json) and the [pull request](docs/specs/12/pr.md). The case and
its data are invented; the shape is exactly the one the flow produces.

### Concepts

| Term | What it means here |
|---|---|
| **Harness** | Everything around the agent that turns raw autonomy into directed work: commands, gates, templates, verifiers. The model is the engine; the harness is the chassis, the brakes and the dashboard. Here it is `tools/` plus `.claude/`. |
| **SDD** — Spec-Driven Development | The spec rules; the chat is disposable. Before a line of code, the request becomes a specification reviewed by a human. Whatever the agent filled in by itself is marked `DECISIÓN` so you see it. |
| **RDD** — Receipt-Driven Development | The review is not an opinion: it is a receipt tied to a fingerprint — the sha of the spec, which tests were seen failing and passing, which gates ran. Change one byte and the receipt is invalid. |
| **Gate** | A verifier that runs by itself: does the class load? who imports whom? House rule: a check that returns zero is worth nothing until you have seen it return one. |
| **Spec (7 axes)** | The central artifact: a fixed form of eight questions — chain impact, schema, scope, owning service, contract, return, tests, acceptance criteria. It lives in the issue and is frozen with a sha when the tests start. |
| **Receipt** | The document `/implementar` leaves when it closes. It is emitted by the instrument (`recibo.sh`), not by the agent's narration, with a state: `APROBABLE` · `DEGRADADO` · `INCONCLUSO` · `FALLANDO`. It travels inside the pull request. |
| **Seam** | The single, decisive point where the feature touches reality and is validated from outside: the endpoint that answers, the query against the live database. It demands a value only the correct referent can produce — not that "something shows up". |
| **Triangulate** | With green in hand, look on purpose for one case that breaks what was *built*. The spec's tests question what was promised; this one questions what was actually written. |
| **Mutant** | A defect planted on purpose to see whether the checks catch it. If it survives with everything green, the check watches nothing. Every gate here was fed a known-bad file before being trusted. |
| **Three anchors** | A referent (foreign key, catalogue, source of a select) is only verified with all three: **data** (a query against the live table), **use** (the current consumer, `file:line`) and **discrimination** (every homonym opened and discarded in writing). |

### What is in the box

| Path | What it is |
|---|---|
| [`CLAUDE.md`](CLAUDE.md) | The doctrine: the pieces, the direction rules, the traps, the style. Loaded into every session. |
| [`.claude/agents/ddd-backend.md`](.claude/agents/ddd-backend.md) | The implementing agent: the exact template of each piece. |
| [`.claude/commands/grill.md`](.claude/commands/grill.md) | `/grill` — the interview that turns a premise into a spec and an issue. |
| [`.claude/commands/implementar.md`](.claude/commands/implementar.md) | `/implementar` — red, code, green, triangulate, receipt. |
| [`.claude/commands/pr.md`](.claude/commands/pr.md) | `/pr` — the pull request description, built from the real diff and the receipt. |
| [`docs/plantilla-7ejes.md`](docs/plantilla-7ejes.md) | The spec template. |
| [`docs/glosario.md`](docs/glosario.md) | The domain glossary: which table each word is, and which homonym it is not. |
| `tools/architecture/` | The gates: `loads.sh` (every class declares), `layers.sh` (who imports whom, naming, service rules), `phpcs.sh` (PSR-12). |
| `tools/harness/` | The receipt kit: `recibo.sh`, `spec-check.sh`, `spec-dump.sh`, and the `commit-msg.sh` hook. |

The commands and the spec artifacts are written in Spanish; the doctrine and the code are in
English.

### What you need

- PHP ^8.3 and `composer install` — the gates need the autoloader and `phpcs`.
- [Claude Code](https://claude.com/claude-code), which picks up `CLAUDE.md`, the agent and the
  commands from this repository by itself.
- Optional: the [`gh`](https://cli.github.com/) CLI or a GitHub MCP server, to create issues and
  pull requests without leaving the session. `spec-dump.sh` reads public issues without a
  token; private repositories need `GITHUB_TOKEN`.

The interview in `/grill` builds on Matt Pocock's grilling skills, which are worth installing
on their own: [`grill-me`](https://github.com/mattpocock/skills/tree/main/skills/productivity/grill-me),
[`grill-with-docs`](https://github.com/mattpocock/skills/tree/main/skills/engineering/grill-with-docs)
and [`tdd`](https://github.com/mattpocock/skills/tree/main/skills/engineering/tdd), from
[mattpocock/skills](https://github.com/mattpocock/skills). `/grill` adds the three anchors, the
measured interview log and the collapse into the 7-axis spec on top of that idea.

### Running it

```bash
composer install
printf '#!/usr/bin/env bash\nexec bash "$(git rev-parse --show-toplevel)/tools/harness/commit-msg.sh" "$@"\n' > .git/hooks/commit-msg

# the gates, on what you changed against the base branch
bash tools/architecture/loads.sh
bash tools/architecture/layers.sh
bash tools/architecture/phpcs.sh
```

Then, in a Claude Code session: `/grill <the request>`, approve the spec, and
`/implementar <issue number>`.

To adopt the flow in a project of your own, copy `.claude/`, `tools/`, `docs/plantilla-7ejes.md`
and `docs/glosario.md`, declare the repositories of your chain in `tools/harness/cadena.txt`
(one per line), and rewrite `CLAUDE.md` against your own tree — the rules that matter are the
ones you measured on your code.

## License

Distributed under the GPL v.2 license. See [LICENSE](LICENSE) for more information.
