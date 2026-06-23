<?php

namespace Georgeff\Schema;

use Closure;

final class Column
{
    public private(set) bool $isPrimary = false;

    public private(set) bool $isIncrementing = false;

    public private(set) bool $isNullable = false;

    public private(set) bool $isUnsigned = false;

    public private(set) bool $hasDefault = false;

    public private(set) mixed $defaultValue = null;

    /**
     * @var array<string, int|string>
     */
    public private(set) array $options = [];

    /**
     * @param Closure(IndexType, string, ?string): void $indexRegistry
     */
    public function __construct(
        public readonly string $name,
        public readonly ColumnType $type,
        private readonly Closure $indexRegistry
    ) {}

    public function primary(?string $name = null): self
    {
        ($this->indexRegistry)(IndexType::Primary, $this->name, $name);

        $this->isPrimary = true;

        return $this;
    }

    public function incrementing(): self
    {
        $this->isIncrementing = true;

        return $this;
    }

    public function nullable(bool $value = true): self
    {
        $this->isNullable = $value;

        return $this;
    }

    public function unsigned(): self
    {
        $this->isUnsigned = true;

        return $this;
    }

    public function unique(?string $name = null): self
    {
        ($this->indexRegistry)(IndexType::Unique, $this->name, $name);

        return $this;
    }

    public function index(?string $name = null): self
    {
        ($this->indexRegistry)(IndexType::Index, $this->name, $name);

        return $this;
    }

    public function default(mixed $value = null): self
    {
        $this->hasDefault = true;

        $this->defaultValue = $value;

        return $this;
    }

    public function defaultRaw(string $sql): self
    {
        return $this->default(new RawExpression($sql));
    }

    public function addOption(string $name, int|string $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }
}
