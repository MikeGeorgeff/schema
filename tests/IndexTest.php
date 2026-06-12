<?php

namespace Georgeff\Schema\Test;

use Georgeff\Schema\Index;
use Georgeff\Schema\IndexType;
use PHPUnit\Framework\TestCase;

final class IndexTest extends TestCase
{
    public function test_constructor_sets_all_properties(): void
    {
        $index = new Index(IndexType::Unique, 'users', 'email', 'users_email_unique');

        $this->assertSame(IndexType::Unique, $index->type);
        $this->assertSame('users', $index->table);
        $this->assertSame('email', $index->column);
        $this->assertSame('users_email_unique', $index->name);
    }

    public function test_name_defaults_to_null(): void
    {
        $index = new Index(IndexType::Index, 'users', 'status');

        $this->assertNull($index->name);
    }

    public function test_primary_index_type(): void
    {
        $index = new Index(IndexType::Primary, 'users', 'id');

        $this->assertSame(IndexType::Primary, $index->type);
    }
}
