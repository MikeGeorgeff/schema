<?php

namespace Georgeff\Schema\Compiler;

use Georgeff\Schema\Blueprint;

interface CompilerInterface
{
    /**
     * @return string[]
     */
    public function create(Blueprint $blueprint): array;

    public function drop(string $table, bool $ifExists = false): string;

    /**
     * @return string[]
     */
    public function alter(Blueprint $blueprint): array;
}
