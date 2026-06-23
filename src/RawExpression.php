<?php

namespace Georgeff\Schema;

final class RawExpression
{
    public function __construct(public readonly string $sql) {}
}
