<?php

namespace Georgeff\Schema;

final class Index
{
    public function __construct(
        public readonly IndexType $type,
        public readonly string $table,
        public readonly string $column,
        public readonly ?string $name = null
    ) {}
}
