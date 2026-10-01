# CLAUDE.md — DDD Foundation

PHP ^8.3, PSR-12, PSR-4 `Spineda\DddFoundation\` → `src/`. The architectural base classes for a
layered, Domain-Driven project: entities, repositories, services, aggregates, value objects.
**It exposes services, not routes:** no controllers, no HTTP, no framework wiring lives here.

This repository ships the **foundation** — the abstract classes and contracts — and the
**flow** used to build on top of it (`/grill` → spec in the issue → `/implementar` → red first
→ receipt → `/pr`). A project built on it adds its own domains under `src/{Layer}/{Domain}/`.

Two different things get copied, and mixing them is the classic mistake:

- **Domain comes from the neighbourhood.** Open two or three files of the same domain before
  writing: which tables, which columns, what the domain calls things. No template gives you that.
- **Form comes from this document and from `.claude/agents/ddd-backend.md`.** Not from whatever
  file happens to sit next to your change. A neighbourhood can carry debt, and imitating it
  spreads it.

## The pieces

| Piece | Path | Extends / implements |
|---|---|---|
| Entity | `src/Entities/{Domain}/{X}.php` | `Entities\AbstractEntity` |
| Persistency | `src/Persistencies/Eloquent/{Domain}/{X}.php` | `Persistencies\Eloquent\AbstractModel` |
| Contract | `src/Contracts/Repositories/{Domain}/{X}Repository.php` | `Contracts\Repositories\AbstractRepositorySingleKey` |
| ORM Repository | `src/Repositories/Database/ORM/{Domain}/ORM{X}Repository.php` | `Repositories\Database\ORM\ORMAbstractRepository` |
| Factory | `src/Factories/Repositories/{Domain}/{X}Factory.php` | `Factories\AbstractFactory` |
| Service | `src/Services/{Domain}/{X}Service.php` | `Contracts\IsService` |
| Aggregate | `src/Aggregates/{Domain}/{X}Aggregate.php` | `Contracts\IsAggregate` |
| Value Object | `src/ValueObjects/{Domain}/{X}.php` | `Contracts\ValueObjects\Collections\IsValueObject` |
| Exception | `src/Exceptions/{Domain}/{Entity}/{X}NotFoundException.php` | `Exceptions\NotFoundException` |

Layout is **layer first, domain second**. Never a feature folder.

## The exemplars — copy the form from these

One small domain, `Shipping`, exists so every piece has a file that loads, passes the gates and
is short enough to read whole. It is a read-by-key exemplar: no pagination, no writes.

| Piece | Exemplar |
|---|---|
| Entity | `src/Entities/Shipping/Driver.php` |
| Persistency | `src/Persistencies/Eloquent/Shipping/Driver.php` |
| Contract | `src/Contracts/Repositories/Shipping/DriverRepository.php` |
| ORM Repository | `src/Repositories/Database/ORM/Shipping/ORMDriverRepository.php` |
| Factory | `src/Factories/Repositories/Shipping/DriverFactory.php` |
| Service | `src/Services/Shipping/DriverService.php` |
| Exception | `src/Exceptions/Shipping/Driver/DriverNotFoundException.php` |

## Fully-qualified names you will need

Copy these verbatim. Guessing a namespace produces code that looks right and does not load.
Every name below exists in `src/`; `bash tools/architecture/loads.sh --all` proves it.

**A name in this list is a spelling, not a licence.** It says the class exists, not that your
case should use it.

```
Spineda\DddFoundation\Entities\AbstractEntity
Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel
Spineda\DddFoundation\Repositories\Database\ORM\ORMAbstractRepository
Spineda\DddFoundation\Repositories\Database\ORM\Behaviors\ORMCrudRepository   // fill-and-save over a model
Spineda\DddFoundation\Factories\AbstractFactory
Spineda\DddFoundation\Contracts\IsService
Spineda\DddFoundation\Contracts\IsAggregate
Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject
Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsCollectable
Spineda\DddFoundation\Contracts\Repositories\AbstractRepositorySingleKey    // also TwoKey, TripleKey, FourKey, FiveKey
Spineda\DddFoundation\Contracts\Repositories\PaginatedListRepository       // declares listPaginated()
Spineda\DddFoundation\Contracts\Repositories\SummarizableRepository        // declares getSummaryInfo()
Spineda\DddFoundation\Contracts\Repositories\Behaviors\CreatableRepository
Spineda\DddFoundation\Contracts\Repositories\Behaviors\UpdatableRepository
Spineda\DddFoundation\Contracts\Repositories\Behaviors\DeletableRepository
Spineda\DddFoundation\Exceptions\NotFoundException      // $code = 404. BASE: extend it
Spineda\DddFoundation\Exceptions\ConflictException      // $code = 409. BASE: extend it
Spineda\DddFoundation\Exceptions\GenericException       // $code = 500. BASE: extend it
Spineda\DddFoundation\ValueObjects\Collections\EntityCollection
Spineda\DddFoundation\ValueObjects\Collections\ValueObjectCollection
Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection
Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection    // the canonical paginated return
Spineda\DddFoundation\ValueObjects\Collections\PaginatedValueObjectCollection
Spineda\DddFoundation\ValueObjects\Data\FiltersInfo
Spineda\DddFoundation\ValueObjects\Data\JoinInfo
Spineda\DddFoundation\ValueObjects\Data\SortInfo
Spineda\DddFoundation\ValueObjects\Data\SummaryInfo
Spineda\DddFoundation\Aggregates\AggregateCollectionWithPaginatedListingAggregate   // the listing wrapper
Spineda\DddFoundation\Traits\Entities\CommonEntity             // id + getId()
Spineda\DddFoundation\Traits\Entities\AuditableEntity          // created_at/by, updated_at/by (Carbon getters)
Spineda\DddFoundation\Traits\Entities\SoftDeletableEntity      // deleted_at/by
Spineda\DddFoundation\Traits\Entities\ActivableEntity          // active
Spineda\DddFoundation\Services\System\MainConfigurationService
```

**The three exception bases are extended, never instantiated.** The shape is
`src/Exceptions/{Domain}/{Entity}/{X}Exception.php`: a class with its own `$message` in Spanish
and nothing else. `{X}NotFoundException extends NotFoundException` for something that is not
there; a domain-named subclass of `ConflictException` for a state that refuses the write. A
**bad argument** is neither: that is `InvalidArgumentException` with code 406, thrown inline.

## Direction rules — these do not appear in any single file

1. **Transactions live in the Service, never in a repository.** A repository method is roughly
   one statement. **One write on one table goes without a transaction**; wrap only several
   writes that must land together. A validation read before the write is not a reason: only a
   constraint makes a check atomic. Gates `B3` and `A9`.
2. **Repositories and Aggregates never import a Factory.** **Entities do** — they resolve a
   foreign key lazily through one. Seeing it in an entity does not license it in a repository:
   if a repository needs another domain's data, the Service composes it. Gates `A1`, `A2`.
3. **Repositories know exactly one Service: `MainConfigurationService`.** Gate `B2`.
4. **Services type against Contracts — the non-database ones too.** A concrete repository
   implements its contract. Gate `B4`.
5. **Entities, Aggregates and ValueObjects never import `Repositories` or `Services`.** Gates
   `A3`, `B1`, `A4`.
6. **Services do compose other Services.** Deliberate, not a smell.
7. **An ORM repository never queries through a direct connection.** New code uses the model's
   query builder; base-table filters travel in `FiltersInfo` built by the caller — which also
   means **scalar values on base-table columns only** (`processFiltersToQuery` does a plain
   `where`). Gate `B5`.
8. **A Service persists only its own entity.** If `{X}Service` receives a `{Y}` entity and hands
   it to `{y}Repository->create()`/`update()`, that CRUD belongs in `{Y}Service` — one service
   per entity, composed when needed. Gate `A8`.

## The trap that breaks builds: composing a behaviour obliges you to implement it

If the contract extends `CreatableRepository`, `UpdatableRepository` or `DeletableRepository`,
the ORM repository **must** define `create()`, `update()` and `delete()` with **exactly** the
declared signature:

```php
public function create(AbstractEntity $entity): int;
public function update(mixed $primaryKey, AbstractEntity $entity): void;
public function delete(mixed $primaryKey): bool;
```

Otherwise PHP fatals with *"contains N abstract methods"* or *"must be compatible with"* — the
file passes every style check and the class cannot be declared. Only compose the behaviour you
are actually going to implement.

**A contract over a relational table always carries its key arity and the basics — `get()` and
`findById()` — even when the feature only inserts.**

**The same trap, second form: key arity.** `AbstractRepositorySingleKey` declares
`get(mixed $key): AbstractEntity`; `TwoKey` declares `get(mixed $key1, mixed $key2)`, and so on.
Implement `get()` with **exactly** that many `mixed` parameters; the return type may be narrowed
to your entity (`: Driver`), never widened or dropped. The arity is the **identity of the
table** — the same columns as the entity's `$keyFields` — not the key you search by. Gate `A7`.

## Pagination — pick one of the two forms, they are not interchangeable

**First form: extend the interface and implement it verbatim.**

```php
interface DriverRepository extends AbstractRepositorySingleKey, PaginatedListRepository
```

`PaginatedListRepository` already declares the method. Do **not** redeclare it, do **not**
rename it, do **not** add a parameter:

```php
public function listPaginated(
    string $orderBy,
    int $perPage,
    int $page = 1,
    bool $reversed = false,
    ?FiltersInfo $filters = null
): PaginatedEntityCollection;
```

**Second form: declare your own `listPaginated{Entity}()` and do not extend
`PaginatedListRepository`.** This is the one to choose when you need extra parameters — an
array of pre-resolved codes, a boolean the filters cannot carry. Same return type. Extra data
never gets stuffed into `FiltersInfo`.

Return `EntityCollection` for lists of entities, `ValueObjectCollection` for projections,
`SummaryInfo` for a single aggregated figure.

## Canonical repository method

```php
<?php

namespace Spineda\DddFoundation\Repositories\Database\ORM\Shipping;

use InvalidArgumentException;
use Spineda\DddFoundation\Contracts\Repositories\Shipping\DriverRepository as Contract;
use Spineda\DddFoundation\Entities\Shipping\Driver;
use Spineda\DddFoundation\Exceptions\Shipping\Driver\DriverNotFoundException;
use Spineda\DddFoundation\Repositories\Database\ORM\ORMAbstractRepository;
use Spineda\DddFoundation\ValueObjects\Collections\EntityCollection;

/**
 * Driver repository
 *
 * @package Spineda\DddFoundation
 */
class ORMDriverRepository extends ORMAbstractRepository implements Contract
{
    /**
     * @var  array
     */
    protected array $searchFieldsLike = ['first_name', 'last_name'];

    /**
     * @var  array
     */
    protected array $searchFieldsDirect = [];

    /**
     * {@inheritDoc}
     * @see  Contract::findByCode()
     */
    public function findByCode(string $code): ?Driver
    {
        if (trim($code) === '') {
            throw new InvalidArgumentException('El código de conductor no es válido', 406);
        }

        $record = $this->model->newQuery()
            ->where('code', $code)
            ->first();

        // Not found, early return
        if (null === $record) {
            return null;
        }

        return new Driver($record->getAttributes());
    }

    /**
     * {@inheritDoc}
     * @see  Contract::getByDepot()
     */
    public function getByDepot(int $depotId): EntityCollection
    {
        if ($depotId <= 0) {
            throw new InvalidArgumentException('El depósito proporcionado no es válido', 406);
        }

        $records = $this->model->newQuery()
            ->where('depot_id', $depotId)
            ->get();

        if ($records->isEmpty()) {
            throw new DriverNotFoundException();
        }

        $collection = new EntityCollection();

        foreach ($records as $record) {
            $collection->add(new Driver($record->getAttributes()));
        }

        return $collection;
    }
}
```

- **A method that can return `null` is named `find`, never `get`.** `find*` is internal and may
  come back empty; `get*` is what reaches the outside and **controls the error** — it throws
  the `{Entity}NotFoundException` instead of returning `null`. Gate `F1`.
- **`create()` and `update()` always receive an entity, never an array.**
- Contract imported **aliased as `Contract`**; every implementation carries `{@inheritDoc}` and
  `@see  Contract::method()`.
- **Argument guard first**: `InvalidArgumentException`, code **406**, message in Spanish. Guards
  are **inline** at the top of the method — never a private `validate*()` helper that only
  throws. Gate `A10`.
- Hydrate explicitly with `new Entity($record->getAttributes())`.
- **Method order: reads first, CRUD last** — `get()`, `find*()`, `listPaginated()`, then
  `create()`, `update()`, `delete()`.
- **Query chains go vertical**: `newQuery()` closes the line and every `->where()` /
  `->orderBy()` / `->first()` sits on its own line; only `->find($id)` stays inline. Gate `B6`.
- Filters, joins and search go through `processFiltersToQuery($query, $filters, $joinInfo)`
  inherited from `ORMAbstractRepository`, with `$searchFieldsLike` / `$searchFieldsDirect`
  declared. It only searches columns of the base table: for a joined table's column write the
  closure explicitly. **A `$searchFieldsDirect` over an integer column must be exercised with
  text in a test** — it compares the search text with `=`.

## Silent traps of this codebase

1. **`AbstractEntity::__construct` drops unknown fields without a sound.** It assigns only
   properties that exist. A typo in a `SELECT` alias produces a half-filled entity and no error
   — unless the field is in `$required`, where `validate()` throws `UnderflowException`.
   **Always declare `$required` with every NOT NULL column**, and `$keyFields` with the
   identity. Both are typed here: `protected array $required`, `protected array $keyFields`.
   Gate `T2`.
2. **`EntityCollection::add()` throws `OverflowException` on a repeated key.** A JOIN that
   duplicates rows fails at hydration, not in production. Fix the query, never the catch.
3. Property names are **database column names** (snake_case); getters are camelCase.
4. **`IsFactory` returns `mixed` on purpose.** A factory returns what it builds; narrow the
   return type in your factory (`public static function get(): DriverRepository`).

## Decisions not written anywhere else

- **Entity** = a table row, has identity (`$keyFields`). **Value Object** = a computed or
  projected shape, explicit typed constructor arguments, no identity. **Aggregate** = the
  composition returned for one response: entities + value objects, typed properties and
  getters, **no branching**.
- Aggregate naming is a grammar: `{X}Aggregate` · `{X}CreateAggregate` · `{X}UpdateAggregate`
  · `{X}ListingAggregate` · `{X}With{Y}Aggregate` · `{X}With{Y}ListingAggregate`.
- **A listing that shows data from another table composes it row by row.** It does not return
  raw foreign keys for the consumer to resolve, and it does not preload a dictionary. The
  Service walks the paginated collection, resolves each foreign key through the **entity's lazy
  getter**, and adds one `{X}With{Y}Aggregate` per row into an `AggregateCollection`. The
  **N+1 is deliberate** — one query per foreign key per row — and it is priced in because the
  listing is paginated; **no comment in the code saying so**. Returning ids, or preloading a
  dictionary, are deviations that need a written cause in the pull request.
- **The listing aggregate itself already exists: return
  `AggregateCollectionWithPaginatedListingAggregate`.** It takes exactly
  `($aggregateCollection, ?$paginated, ?$filters)` and exposes `getAggregateCollection()`,
  `getPaginated()`, `getFilters()`. **Do not write a `{X}With{Y}ListingAggregate`** — the
  `{X}With{Y}Aggregate` for the *row* is yours to write, the listing wrapper is not. Roll your
  own **only** if the listing genuinely needs a fourth getter, and say in the pull request
  which one. Gate `A6`.
- **`IsCollectable` is about iterating a collection, not about serialising one.**
  `Collection::jsonSerialize()` walks the raw array and needs nothing. `Collection::rewind()`
  **does** declare `: ?IsCollectable`, so the moment anything writes
  `foreach ($collection as $row)` the row aggregate must implement it or the loop fatals with a
  `TypeError`. Add the interface when the rows are going to be iterated — and note that a test
  that iterates makes it required.
- **An entity's lazy foreign-key getter is nullable exactly when its column is.** A nullable
  column gets `?Entity` resolved through `find*`; the non-null form over a throwing `get()`
  blows up the listing that composes it. Getting this wrong on a column you just added is worse
  than usual: **every row is untyped the day the column is added.**
- A contract declares **key arity** and **capabilities** (the `Behaviors\*` interfaces)
  separately.

## Style

- Docblock on every property (`@var`) and every method (`@param`/`@return`/`@throws`), with
  `@package Spineda\DddFoundation` on every class. Tags are followed by two spaces and
  parameters are aligned in columns:
  ```php
  /**
   * Gets an entity from this repository using its key
   *
   * @param   mixed  $key1  Key 1 argument
   * @param   mixed  $key2  Key 2 argument
   *
   * @return  AbstractEntity
   * @throws  NotFoundException
   */
  ```
  The description is **one line**. What needs a paragraph belongs in the spec, not in the code.
- Inline comments are short English sentences starting with a capital
  (`// Not found, early return`).
- Identifiers and comments in **English**; anything a human reads at runtime in **Spanish**
  (exception messages, assertion messages). Gate `N`.
- Comments cite decisions (`D7`), never issues (`#12`): those live in the pull request and in
  `docs/specs`, and in the code they rot. **Nor evidence**: no counts with a unit, no ISO dates
  in a comment or a test docblock. Gates `A11`, `A13`.
- Enum-like values are **literals** where used (`'shift_end'`, `'gate'`), never class constants.
  Gate `A12`.
- `Carbon` for dates. No `declare(strict_types=1)` (gate `A5`), no `readonly`, no `final`.
- **No constructor property promotion in an Entity** — it breaks `AbstractEntity`'s array
  constructor.
- PSR-12, lines ≤120, exactly one blank line between methods, one trailing newline, no closing
  `?>`.
- Never `DB::`, `print_r`, `var_dump`, `dd()`.
- Tests: camelCase methods (`testFindsByCode`), `static::assert…`, assertion messages in
  Spanish, the `// Performs the test.` / `// Performs assertions.` comments, one test class per
  class under the mirrored path in `tests/Unit/`.
- **Mock the collections that go IN; keep the collection that comes OUT real.** An iterable
  collection used as input is mocked with `mockIterator()` over `mockWithoutConstructor()`
  (`tests/AbstractTest.php`). The collection the method under test **returns** stays real:
  mocking both ends means the assertions never run against what the method actually built.
- **Commit subjects are `:gitmoji_shortcode: Short subject`** — `:recycle: applyJoinInfoToQuery`,
  `:white_check_mark: FileTest` — with no assistant trailers. Enforced by
  `tools/harness/commit-msg.sh`; install it once per clone as `.git/hooks/commit-msg` (one-liner
  in its header).
- Do not add new abstract classes or traits **outside the pieces above**.

## Before you finish

Run the gates on what you wrote. They are the same ones the receipt runs, so anything they flag
here is a failing receipt later:

```
bash tools/architecture/loads.sh    # every class you touched can actually be declared
bash tools/architecture/layers.sh   # who imports whom, the naming rules, the service rules
bash tools/architecture/phpcs.sh    # PSR-12 + one blank line between methods
```

None needs arguments: they work out the changed files against the base branch themselves,
including new files you have not added yet. Pass paths to check something specific, or `--all`
to sweep every file. Known debt is listed by file in `tools/architecture/excepciones.txt`.

## Three ways a document like this goes wrong

Each one cost a real defect where this flow was born.

1. **A rule written from impression instead of counted against the tree.** Count first, then
   write the number next to the rule so the next reader can re-check it. This file carries no
   counts because the foundation has no domain yet; when your project grows one, measure your
   own tree and write the numbers in.
2. **A check that returns zero is worth nothing until you have seen it return one.** Feed every
   new check something that must make it fire.
3. **An agent's own report is not evidence.** Run the gates yourself and read the diff.
