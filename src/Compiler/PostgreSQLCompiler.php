<?php

namespace Georgeff\Schema\Compiler;

use Georgeff\Schema\Index;
use Georgeff\Schema\Column;
use Georgeff\Schema\IndexType;
use Georgeff\Schema\Blueprint;
use Georgeff\Schema\ColumnType;
use Georgeff\Schema\ForeignKey;

final class PostgreSQLCompiler extends AbstractCompiler
{
    public function create(Blueprint $blueprint): array
    {
        $columns = array_map(fn(Column $column) => $this->compileColumn($column), $blueprint->columns);

        $primaryIndex = $this->findPrimaryIndex($blueprint);

        if (null !== $primaryIndex) {
            $columns[] = $this->compilePrimaryKey($primaryIndex);
        }

        foreach ($blueprint->foreignKeys as $fk) {
            $columns[] = $this->compileForeignKey($fk, $blueprint->table);
        }

        $statements = [
            sprintf("CREATE TABLE %s (\n    %s\n);", $this->quoteIdentifier($blueprint->table), implode(",\n    ", $columns)),
        ];

        foreach ($blueprint->indexes as $index) {
            if (IndexType::Primary !== $index->type) {
                $statements[] = $this->compileIndex($index);
            }
        }

        return $statements;
    }

    public function alter(Blueprint $blueprint): array
    {
        $statements = [];

        foreach ($blueprint->columns as $column) {
            $statements[] = sprintf(
                'ALTER TABLE %s ADD COLUMN %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->compileColumn($column)
            );
        }

        foreach ($blueprint->dropColumns as $column) {
            $statements[] = sprintf(
                'ALTER TABLE %s DROP COLUMN %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->quoteIdentifier($column)
            );
        }

        foreach ($blueprint->dropIndexes as $index) {
            $statements[] = sprintf('DROP INDEX %s;', $this->quoteIdentifier($index));
        }

        foreach ($blueprint->dropForeignKeys as $fk) {
            $statements[] = sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->quoteIdentifier($fk)
            );
        }

        foreach ($blueprint->indexes as $index) {
            $statements[] = $this->compileIndex($index);
        }

        foreach ($blueprint->foreignKeys as $fk) {
            $statements[] = sprintf(
                'ALTER TABLE %s ADD %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->compileForeignKey($fk, $blueprint->table)
            );
        }

        return $statements;
    }

    protected function compileBooleanDefault(bool $value): string
    {
        return $value ? 'TRUE' : 'FALSE';
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '"' . $identifier . '"';
    }

    private function compileColumn(Column $column): string
    {
        $parts = [
            $this->quoteIdentifier($column->name),
            $this->compileType($column),
        ];

        if ($column->isNullable) {
            $parts[] = 'NULL';
        } else {
            $parts[] = 'NOT NULL';
        }

        if ($column->hasDefault) {
            $parts[] = 'DEFAULT ' . $this->compileDefault($column->defaultValue);
        }

        return implode(' ', $parts);
    }

    private function compileType(Column $column): string
    {
        return match ($column->type) {
            ColumnType::BigInt    => $column->isIncrementing ? 'BIGSERIAL' : 'BIGINT',
            ColumnType::RegInt    => $column->isIncrementing ? 'SERIAL' : 'INTEGER',
            ColumnType::SmallInt  => 'SMALLINT',
            ColumnType::TinyInt   => 'SMALLINT',
            ColumnType::Real      => 'REAL',
            ColumnType::Decimal   => sprintf('DECIMAL(%d, %d)', (int) $column->options['precision'], (int) $column->options['scale']),
            ColumnType::Char      => sprintf('CHAR(%d)', (int) $column->options['length']),
            ColumnType::Varchar   => sprintf('VARCHAR(%d)', (int) $column->options['length']),
            ColumnType::Text      => 'TEXT',
            ColumnType::BoolType  => 'BOOLEAN',
            ColumnType::Json      => 'JSON',
            ColumnType::Uuid      => 'UUID',
            ColumnType::Date      => 'DATE',
            ColumnType::Time      => 'TIME',
            ColumnType::Timestamp => 'TIMESTAMP',
        };
    }

    private function compilePrimaryKey(Index $index): string
    {
        return sprintf(
            'CONSTRAINT %s PRIMARY KEY (%s)',
            $this->quoteIdentifier($this->indexName($index)),
            $this->quoteIdentifier($index->column)
        );
    }

    private function compileIndex(Index $index): string
    {
        $unique = IndexType::Unique === $index->type ? 'UNIQUE ' : '';

        return sprintf(
            'CREATE %sINDEX %s ON %s (%s);',
            $unique,
            $this->quoteIdentifier($this->indexName($index)),
            $this->quoteIdentifier($index->table),
            $this->quoteIdentifier($index->column)
        );
    }

    private function compileForeignKey(ForeignKey $fk, string $table): string
    {
        if (null === $fk->on || null === $fk->references) {
            throw new \LogicException('Foreign key constraint must contain a reference table and column');
        }

        return sprintf(
            'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            $this->quoteIdentifier($this->foreignKeyName($fk, $table)),
            $this->quoteIdentifier($fk->column),
            $this->quoteIdentifier($fk->on),
            $this->quoteIdentifier($fk->references),
            $fk->onDelete,
            $fk->onUpdate
        );
    }
}
