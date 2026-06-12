# georgeff/schema

Database schema builder for PHP. Define table structures using a fluent blueprint API and compile them to SQL for MySQL, PostgreSQL, or SQLite. Produces raw SQL strings — no connection required.

## Installation

```bash
composer require georgeff/schema
```

## Overview

`georgeff/schema` separates schema definition from execution. You build a `Blueprint`, pass it to a compiler, and receive an array of SQL strings to run however you like.

```php
use Georgeff\Schema\Blueprint;
use Georgeff\Schema\Compiler\PostgreSQLCompiler;

$blueprint = new Blueprint('users');
$blueprint->id();
$blueprint->string('email')->unique();
$blueprint->string('name');
$blueprint->timestamps();

$compiler = new PostgreSQLCompiler();

foreach ($compiler->create($blueprint) as $sql) {
    // execute $sql against your connection
}
```

## Compilers

Three compilers are available, each implementing `CompilerInterface`:

```php
use Georgeff\Schema\Compiler\MySQLCompiler;
use Georgeff\Schema\Compiler\PostgreSQLCompiler;
use Georgeff\Schema\Compiler\SQLiteCompiler;
```

The MySQL compiler accepts optional constructor arguments to override the table options:

```php
$compiler = new MySQLCompiler(
    engine:  'InnoDB',           // default
    charset: 'utf8mb4',          // default
    collate: 'utf8mb4_unicode_ci' // default
);
```

## Compiler Methods

### `create(Blueprint $blueprint): string[]`

Returns one or more SQL statements to create the table. MySQL always returns a single statement with indexes inline. PostgreSQL and SQLite return additional `CREATE INDEX` statements when non-primary indexes are present.

```php
$statements = $compiler->create($blueprint);
// ['CREATE TABLE ...', 'CREATE UNIQUE INDEX ...']
```

### `drop(string $table, bool $ifExists = false): string`

```php
$compiler->drop('users');
// DROP TABLE "users";

$compiler->drop('users', ifExists: true);
// DROP TABLE IF EXISTS "users";
```

### `alter(Blueprint $blueprint): string[]`

Returns one statement per change. Used for adding/dropping columns, indexes, and foreign keys on an existing table.

```php
$blueprint = new Blueprint('users');
$blueprint->string('phone')->nullable();
$blueprint->dropColumn('bio');

foreach ($compiler->alter($blueprint) as $sql) {
    // execute $sql
}
```

> **SQLite limitation:** SQLite only supports `ADD COLUMN` and `DROP COLUMN`. Calls to `dropIndex()`, `dropForeign()`, and `foreign()` on a Blueprint passed to `SQLiteCompiler::alter()` are silently ignored.

## Blueprint

### Column Types

| Method | Description |
|---|---|
| `id(string $name = 'id')` | `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `bigInteger(string $name)` | 8-byte integer |
| `integer(string $name)` | 4-byte integer |
| `smallInteger(string $name)` | 2-byte integer |
| `tinyInteger(string $name)` | 1-byte integer (MySQL) / `SMALLINT` (PostgreSQL) |
| `float(string $name)` | Floating point |
| `decimal(string $name, int $precision = 8, int $scale = 2)` | Fixed precision |
| `string(string $name, int $length = 255)` | `VARCHAR` |
| `char(string $name, int $length = 1)` | Fixed-length string |
| `text(string $name)` | Unbounded text |
| `boolean(string $name)` | Boolean (`TINYINT(1)` on MySQL, `BOOLEAN` on PostgreSQL, `INTEGER` on SQLite) |
| `json(string $name)` | JSON (`TEXT` on SQLite) |
| `uuid(string $name)` | UUID (`CHAR(36)` on MySQL, `UUID` on PostgreSQL, `TEXT` on SQLite) |
| `date(string $name)` | Date |
| `time(string $name)` | Time |
| `timestamp(string $name)` | Timestamp (`TEXT` on SQLite) |
| `timestamps()` | Adds nullable `created_at` and `updated_at` timestamp columns |

### Column Modifiers

Modifiers chain off any column factory method:

```php
$blueprint->string('email')
    ->nullable()
    ->default('guest@example.com')
    ->unique();
```

| Modifier | Description |
|---|---|
| `nullable(bool $value = true)` | Allows `NULL`; pass `false` to revert |
| `unsigned()` | `UNSIGNED` (MySQL only; ignored on PostgreSQL and SQLite) |
| `default(mixed $value)` | Sets a default value |
| `incrementing()` | `AUTO_INCREMENT` (MySQL) / `SERIAL`/`BIGSERIAL` (PostgreSQL) / `AUTOINCREMENT` (SQLite) |
| `primary(?string $name = null)` | Marks column as primary key |
| `unique(?string $name = null)` | Adds a unique index |
| `index(?string $name = null)` | Adds a regular index |

### Foreign Keys

```php
$blueprint->bigInteger('user_id');
$blueprint->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('CASCADE')
    ->onUpdate('RESTRICT');
```

An optional constraint name can be passed as the second argument to `foreign()`:

```php
$blueprint->foreign('user_id', 'fk_posts_user_id')
    ->references('id')
    ->on('users');
```

Valid actions for `onDelete` and `onUpdate`: `NO ACTION`, `RESTRICT`, `SET NULL`, `CASCADE`. Both default to `RESTRICT`.

### ALTER Operations

```php
$blueprint = new Blueprint('users');

// Add columns
$blueprint->string('phone')->nullable();

// Drop columns
$blueprint->dropColumn('legacy_field');

// Drop indexes
$blueprint->dropIndex('users_email_unique');

// Drop foreign keys
$blueprint->dropForeign('posts_user_id_foreign');
```

## Index Naming

When no name is provided, indexes are auto-named using the pattern:

```
{table}_{column}_{suffix}
```

Where suffix is `primary`, `unique`, `index`, or `foreign`. For example:

```php
$blueprint->string('email')->unique();
// Index name: users_email_unique

$blueprint->foreign('user_id')->references('id')->on('users');
// Constraint name: posts_user_id_foreign
```

Pass an explicit name to override:

```php
$blueprint->string('email')->unique('my_email_constraint');
```

## Driver Differences

| Feature | MySQL | PostgreSQL | SQLite |
|---|---|---|---|
| Quoting | Backticks | Double quotes | Double quotes |
| Auto-increment | `AUTO_INCREMENT` | `SERIAL` / `BIGSERIAL` | `INTEGER PRIMARY KEY AUTOINCREMENT` |
| Unsigned columns | Supported | Silently ignored | Silently ignored |
| Boolean | `TINYINT(1)` | `BOOLEAN` | `INTEGER` |
| UUID | `CHAR(36)` | `UUID` | `TEXT` |
| Indexes in `CREATE TABLE` | Inline | Separate statements | Separate statements |
| `ALTER` foreign key support | Yes | Yes | No |
