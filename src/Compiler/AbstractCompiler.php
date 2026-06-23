<?php

namespace Georgeff\Schema\Compiler;

use Georgeff\Schema\Index;
use Georgeff\Schema\Blueprint;
use Georgeff\Schema\IndexType;
use Georgeff\Schema\ForeignKey;
use Georgeff\Schema\RawExpression;

abstract class AbstractCompiler implements CompilerInterface
{
    public function drop(string $table, bool $ifExists = false): string
    {
        return sprintf(
            'DROP TABLE %s%s;',
            $ifExists ? 'IF EXISTS ' : '',
            $this->quoteIdentifier($table)
        );
    }

    protected function indexName(Index $index): string
    {
        if (null !== $index->name) {
            return $index->name;
        }

        $suffix = match ($index->type) {
            IndexType::Primary => 'primary',
            IndexType::Unique  => 'unique',
            IndexType::Index   => 'index',
        };

        return sprintf('%s_%s_%s', $index->table, $index->column, $suffix);
    }

    protected function foreignKeyName(ForeignKey $fk, string $table): string
    {
        return $fk->name ?? sprintf('%s_%s_foreign', $table, $fk->column);
    }

    protected function findPrimaryIndex(Blueprint $blueprint): ?Index
    {
        foreach ($blueprint->indexes as $index) {
            if (IndexType::Primary === $index->type) {
                return $index;
            }
        }

        return null;
    }

    protected function compileDefault(mixed $value): string
    {
        return match (true) {
            $value instanceof RawExpression => $value->sql,
            is_null($value)                 => 'NULL',
            is_bool($value)                 => $this->compileBooleanDefault($value),
            is_int($value),
            is_float($value)                => (string) $value,
            default                         => sprintf("'%s'", addslashes(is_string($value) ? $value : '')),
        };
    }

    protected function compileBooleanDefault(bool $value): string
    {
        return $value ? '1' : '0';
    }

    abstract protected function quoteIdentifier(string $identifier): string;
}
