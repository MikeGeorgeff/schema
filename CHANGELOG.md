# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-06-13

### Added

- `tableExists(): string` method on `CompilerInterface` — returns a parameterized SQL query to check whether a table exists. Each compiler returns a driver-appropriate query (`information_schema` for MySQL and PostgreSQL, `sqlite_master` for SQLite).
- `PostgreSQLCompiler` now accepts an optional `string $schema = 'public'` constructor parameter to target a non-default PostgreSQL schema in `tableExists()` queries.

### Upgrade Notes

Custom implementations of `CompilerInterface` must add a `tableExists(): string` method. The method should return a parameterized SQL query string with a single `?` placeholder for the table name.

## [1.0.0] - 2026-06-12

Initial release.
