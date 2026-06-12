<?php

namespace Georgeff\Schema\Compiler;

use Georgeff\Schema\Index;
use Georgeff\Schema\Column;
use Georgeff\Schema\Blueprint;
use Georgeff\Schema\IndexType;
use Georgeff\Schema\ColumnType;
use Georgeff\Schema\ForeignKey;

final class SQLiteCompiler extends AbstractCompiler
{
    public function create(Blueprint $blueprint): array
    {
        $columns = array_map(fn(Column $column) => $this->compileColumn($column), $blueprint->columns);

        $primaryIndex = $this->findPrimaryIndex($blueprint);

        if (null !== $primaryIndex && !$this->isIntegerPrimaryKey($blueprint, $primaryIndex)) {
            $columns[] = sprintf('PRIMARY KEY (%s)', $this->quoteIdentifier($primaryIndex->column));
        }

        foreach ($blueprint->foreignKeys as $fk) {
            $columns[] = $this->compileForeignKey($fk);
        }

        $statements = [
            sprintf(
                "CREATE TABLE %s (\n    %s\n);",
                $this->quoteIdentifier($blueprint->table),
                implode(",\n    ", $columns)
            ),
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

        foreach ($blueprint->indexes as $index) {
            $statements[] = $this->compileIndex($index);
        }

        return $statements;
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
        if ($column->isPrimary && $column->isIncrementing && ColumnType::BigInt === $column->type) {
            return 'INTEGER PRIMARY KEY AUTOINCREMENT';
        }

        return match ($column->type) {
            ColumnType::BigInt,
            ColumnType::RegInt,
            ColumnType::SmallInt,
            ColumnType::TinyInt,
            ColumnType::BoolType  => 'INTEGER',
            ColumnType::Real      => 'REAL',
            ColumnType::Decimal   => sprintf('NUMERIC(%d, %d)', $column->options['precision'], $column->options['scale']),
            ColumnType::Char,
            ColumnType::Varchar   => sprintf('VARCHAR(%d)', $column->options['length']),
            ColumnType::Text,
            ColumnType::Json,
            ColumnType::Uuid,
            ColumnType::Timestamp => 'TEXT',
            ColumnType::Date      => 'DATE',
            ColumnType::Time      => 'TIME',
        };
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

    private function compileForeignKey(ForeignKey $fk): string
    {
        if (null === $fk->on || null === $fk->references) {
            throw new \LogicException('Foreign key constraint must contain a reference table and column.');
        }

        return sprintf(
            'FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            $this->quoteIdentifier($fk->column),
            $this->quoteIdentifier($fk->on),
            $this->quoteIdentifier($fk->references),
            $fk->onDelete,
            $fk->onUpdate
        );
    }

    private function isIntegerPrimaryKey(Blueprint $blueprint, Index $primaryIndex): bool
    {
        foreach ($blueprint->columns as $column) {
            if ($column->name === $primaryIndex->column) {
                return $column->isIncrementing;
            }
        }

        return false;
    }
}
