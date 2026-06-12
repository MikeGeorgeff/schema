<?php

namespace Georgeff\Schema\Test;

use Closure;
use Georgeff\Schema\Column;
use Georgeff\Schema\ColumnType;
use Georgeff\Schema\IndexType;
use PHPUnit\Framework\TestCase;

final class ColumnTest extends TestCase
{
    private function makeColumn(string $name, ColumnType $type, ?Closure $registry = null): Column
    {
        return new Column($name, $type, $registry ?? function () {});
    }

    public function test_constructor_sets_name_and_type(): void
    {
        $column = $this->makeColumn('email', ColumnType::Varchar);

        $this->assertSame('email', $column->name);
        $this->assertSame(ColumnType::Varchar, $column->type);
    }

    public function test_defaults_are_false_and_null(): void
    {
        $column = $this->makeColumn('email', ColumnType::Varchar);

        $this->assertFalse($column->isPrimary);
        $this->assertFalse($column->isIncrementing);
        $this->assertFalse($column->isNullable);
        $this->assertFalse($column->isUnsigned);
        $this->assertFalse($column->hasDefault);
        $this->assertNull($column->defaultValue);
        $this->assertSame([], $column->options);
    }

    public function test_primary_sets_flag_and_returns_self(): void
    {
        $column = $this->makeColumn('id', ColumnType::BigInt);

        $result = $column->primary();

        $this->assertSame($column, $result);
        $this->assertTrue($column->isPrimary);
    }

    public function test_primary_invokes_registry_with_correct_args(): void
    {
        $calls = [];
        $column = $this->makeColumn('id', ColumnType::BigInt, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $column->primary();

        $this->assertCount(1, $calls);
        $this->assertSame(IndexType::Primary, $calls[0][0]);
        $this->assertSame('id', $calls[0][1]);
        $this->assertNull($calls[0][2]);
    }

    public function test_primary_passes_custom_name_to_registry(): void
    {
        $calls = [];
        $column = $this->makeColumn('id', ColumnType::BigInt, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $column->primary('pk_users');

        $this->assertSame('pk_users', $calls[0][2]);
    }

    public function test_incrementing_sets_flag_and_returns_self(): void
    {
        $column = $this->makeColumn('id', ColumnType::BigInt);

        $result = $column->incrementing();

        $this->assertSame($column, $result);
        $this->assertTrue($column->isIncrementing);
    }

    public function test_nullable_sets_flag_and_returns_self(): void
    {
        $column = $this->makeColumn('deleted_at', ColumnType::Timestamp);

        $result = $column->nullable();

        $this->assertSame($column, $result);
        $this->assertTrue($column->isNullable);
    }

    public function test_nullable_false_unsets_flag(): void
    {
        $column = $this->makeColumn('deleted_at', ColumnType::Timestamp);

        $column->nullable()->nullable(false);

        $this->assertFalse($column->isNullable);
    }

    public function test_unsigned_sets_flag_and_returns_self(): void
    {
        $column = $this->makeColumn('count', ColumnType::BigInt);

        $result = $column->unsigned();

        $this->assertSame($column, $result);
        $this->assertTrue($column->isUnsigned);
    }

    public function test_unique_invokes_registry_and_returns_self(): void
    {
        $calls = [];
        $column = $this->makeColumn('email', ColumnType::Varchar, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $result = $column->unique();

        $this->assertSame($column, $result);
        $this->assertCount(1, $calls);
        $this->assertSame(IndexType::Unique, $calls[0][0]);
        $this->assertSame('email', $calls[0][1]);
        $this->assertNull($calls[0][2]);
    }

    public function test_unique_passes_custom_name_to_registry(): void
    {
        $calls = [];
        $column = $this->makeColumn('email', ColumnType::Varchar, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $column->unique('users_email_unique');

        $this->assertSame('users_email_unique', $calls[0][2]);
    }

    public function test_index_invokes_registry_and_returns_self(): void
    {
        $calls = [];
        $column = $this->makeColumn('status', ColumnType::Varchar, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $result = $column->index();

        $this->assertSame($column, $result);
        $this->assertCount(1, $calls);
        $this->assertSame(IndexType::Index, $calls[0][0]);
        $this->assertSame('status', $calls[0][1]);
        $this->assertNull($calls[0][2]);
    }

    public function test_index_passes_custom_name_to_registry(): void
    {
        $calls = [];
        $column = $this->makeColumn('status', ColumnType::Varchar, function (IndexType $type, string $col, ?string $name) use (&$calls) {
            $calls[] = [$type, $col, $name];
        });

        $column->index('users_status_index');

        $this->assertSame('users_status_index', $calls[0][2]);
    }

    public function test_default_sets_flag_and_value_and_returns_self(): void
    {
        $column = $this->makeColumn('status', ColumnType::Varchar);

        $result = $column->default('active');

        $this->assertSame($column, $result);
        $this->assertTrue($column->hasDefault);
        $this->assertSame('active', $column->defaultValue);
    }

    public function test_default_null_is_distinct_from_no_default(): void
    {
        $column = $this->makeColumn('deleted_at', ColumnType::Timestamp);

        $column->default(null);

        $this->assertTrue($column->hasDefault);
        $this->assertNull($column->defaultValue);
    }

    public function test_default_accepts_integer_value(): void
    {
        $column = $this->makeColumn('count', ColumnType::RegInt);

        $column->default(0);

        $this->assertTrue($column->hasDefault);
        $this->assertSame(0, $column->defaultValue);
    }

    public function test_default_accepts_boolean_value(): void
    {
        $column = $this->makeColumn('active', ColumnType::BoolType);

        $column->default(false);

        $this->assertTrue($column->hasDefault);
        $this->assertFalse($column->defaultValue);
    }

    public function test_add_option_stores_value_and_returns_self(): void
    {
        $column = $this->makeColumn('name', ColumnType::Varchar);

        $result = $column->addOption('length', 255);

        $this->assertSame($column, $result);
        $this->assertSame(['length' => 255], $column->options);
    }

    public function test_add_option_stores_multiple_options(): void
    {
        $column = $this->makeColumn('amount', ColumnType::Decimal);

        $column->addOption('precision', 8)->addOption('scale', 2);

        $this->assertSame(['precision' => 8, 'scale' => 2], $column->options);
    }

    public function test_add_option_overwrites_existing_key(): void
    {
        $column = $this->makeColumn('name', ColumnType::Varchar);

        $column->addOption('length', 255)->addOption('length', 100);

        $this->assertSame(['length' => 100], $column->options);
    }

    public function test_modifiers_are_chainable(): void
    {
        $column = $this->makeColumn('email', ColumnType::Varchar);

        $column->nullable()->unique()->default('');

        $this->assertTrue($column->isNullable);
        $this->assertTrue($column->hasDefault);
        $this->assertSame('', $column->defaultValue);
    }
}
