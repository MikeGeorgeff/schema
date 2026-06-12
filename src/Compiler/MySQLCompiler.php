<?php

namespace Georgeff\Schema\Compiler;

use Georgeff\Schema\Index;
use Georgeff\Schema\Column;
use Georgeff\Schema\IndexType;
use Georgeff\Schema\Blueprint;
use Georgeff\Schema\ColumnType;
use Georgeff\Schema\ForeignKey;

final class MySQLCompiler extends AbstractCompiler
{
    public function __construct(
        private readonly string $engine  = 'InnoDB',
        private readonly string $charset = 'utf8mb4',
        private readonly string $collate = 'utf8mb4_unicode_ci'
    ) {}

    public function create(Blueprint $blueprint): array
    {
        $columns = array_map(fn(Column $column) => $this->compileColumn($column), $blueprint->columns);

        foreach ($blueprint->indexes as $index) {
            $columns[] = $this->compileIndex($index);
        }

        foreach ($blueprint->foreignKeys as $fk) {
            $columns[] = $this->compileForeignKey($fk, $blueprint->table);
        }

        return [
            sprintf(
                "CREATE TABLE %s (\n    %s\n) ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s;",
                $this->quoteIdentifier($blueprint->table),
                implode(",\n    ", $columns),
                $this->engine,
                $this->charset,
                $this->collate
            ),
        ];
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
            $statements[] = sprintf(
                'ALTER TABLE %s DROP INDEX %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->quoteIdentifier($index)
            );
        }

        foreach ($blueprint->dropForeignKeys as $fk) {
            $statements[] = sprintf(
                'ALTER TABLE %s DROP FOREIGN KEY %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->quoteIdentifier($fk)
            );
        }

        foreach ($blueprint->indexes as $index) {
            $statements[] = sprintf(
                'ALTER TABLE %s ADD %s;',
                $this->quoteIdentifier($blueprint->table),
                $this->compileIndex($index)
            );
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

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . $identifier . '`';
    }

    private function compileColumn(Column $column): string
    {
        $parts = [
            $this->quoteIdentifier($column->name),
            $this->compileType($column),
        ];

        if ($column->isUnsigned) {
            $parts[] = 'UNSIGNED';
        }

        if ($column->isNullable) {
            $parts[] = 'NULL';
        } else {
            $parts[] = 'NOT NULL';
        }

        if ($column->isIncrementing) {
            $parts[] = 'AUTO_INCREMENT';
        }

        if ($column->hasDefault) {
            $parts[] = 'DEFAULT ' . $this->compileDefault($column->defaultValue);
        }

        return implode(' ', $parts);
    }

    private function compileType(Column $column): string
    {
        return match ($column->type) {
            ColumnType::BigInt    => 'BIGINT',
            ColumnType::RegInt    => 'INT',
            ColumnType::SmallInt  => 'SMALLINT',
            ColumnType::TinyInt   => 'TINYINT',
            ColumnType::Real      => 'FLOAT',
            ColumnType::Decimal   => sprintf('DECIMAL(%d, %d)', $column->options['precision'], $column->options['scale']),
            ColumnType::Char      => sprintf('CHAR(%d)', $column->options['length']),
            ColumnType::Varchar   => sprintf('VARCHAR(%d)', $column->options['length']),
            ColumnType::Text      => 'TEXT',
            ColumnType::BoolType  => 'TINYINT(1)',
            ColumnType::Json      => 'JSON',
            ColumnType::Uuid      => 'CHAR(36)',
            ColumnType::Date      => 'DATE',
            ColumnType::Time      => 'TIME',
            ColumnType::Timestamp => 'TIMESTAMP',
        };
    }

    private function compileIndex(Index $index): string
    {
        return match ($index->type) {
            IndexType::Primary => sprintf(
                'PRIMARY KEY (%s)',
                $this->quoteIdentifier($index->column)
            ),
            IndexType::Unique => sprintf(
                'UNIQUE KEY %s (%s)',
                $this->quoteIdentifier($this->indexName($index)),
                $this->quoteIdentifier($index->column)
            ),
            IndexType::Index => sprintf(
                'KEY %s (%s)',
                $this->quoteIdentifier($this->indexName($index)),
                $this->quoteIdentifier($index->column)
            ),
        };
    }

    private function compileForeignKey(ForeignKey $fk, string $table): string
    {
        if (null === $fk->on || null === $fk->references) {
            throw new \LogicException('Foreign key constraint must contain a reference table and column.');
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
