<?php

namespace Georgeff\Schema\Test;

use Georgeff\Schema\Blueprint;
use Georgeff\Schema\Column;
use Georgeff\Schema\ColumnType;
use Georgeff\Schema\ForeignKey;
use Georgeff\Schema\IndexType;
use PHPUnit\Framework\TestCase;

final class BlueprintTest extends TestCase
{
    public function test_constructor_sets_table(): void
    {
        $blueprint = new Blueprint('users');

        $this->assertSame('users', $blueprint->table);
    }

    public function test_defaults_are_empty_arrays(): void
    {
        $blueprint = new Blueprint('users');

        $this->assertSame([], $blueprint->columns);
        $this->assertSame([], $blueprint->indexes);
        $this->assertSame([], $blueprint->foreignKeys);
        $this->assertSame([], $blueprint->dropColumns);
        $this->assertSame([], $blueprint->dropIndexes);
        $this->assertSame([], $blueprint->dropForeignKeys);
    }

    public function test_id_creates_bigint_primary_unsigned_incrementing_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->id();

        $this->assertSame('id', $column->name);
        $this->assertSame(ColumnType::BigInt, $column->type);
        $this->assertTrue($column->isPrimary);
        $this->assertTrue($column->isUnsigned);
        $this->assertTrue($column->isIncrementing);
        $this->assertCount(1, $blueprint->columns);
        $this->assertCount(1, $blueprint->indexes);
        $this->assertSame(IndexType::Primary, $blueprint->indexes[0]->type);
        $this->assertSame('users', $blueprint->indexes[0]->table);
        $this->assertSame('id', $blueprint->indexes[0]->column);
    }

    public function test_id_accepts_custom_name(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->id('user_id');

        $this->assertSame('user_id', $column->name);
    }

    public function test_string_creates_varchar_column_with_length(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->string('email');

        $this->assertSame('email', $column->name);
        $this->assertSame(ColumnType::Varchar, $column->type);
        $this->assertSame(['length' => 255], $column->options);
    }

    public function test_string_accepts_custom_length(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->string('code', 10);

        $this->assertSame(['length' => 10], $column->options);
    }

    public function test_char_creates_char_column_with_length(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->char('initial');

        $this->assertSame(ColumnType::Char, $column->type);
        $this->assertSame(['length' => 1], $column->options);
    }

    public function test_char_accepts_custom_length(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->char('code', 3);

        $this->assertSame(['length' => 3], $column->options);
    }

    public function test_text_creates_text_column(): void
    {
        $blueprint = new Blueprint('posts');

        $column = $blueprint->text('body');

        $this->assertSame('body', $column->name);
        $this->assertSame(ColumnType::Text, $column->type);
    }

    public function test_integer_creates_regint_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->integer('age');

        $this->assertSame(ColumnType::RegInt, $column->type);
    }

    public function test_tiny_integer_creates_tinyint_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->tinyInteger('status');

        $this->assertSame(ColumnType::TinyInt, $column->type);
    }

    public function test_small_integer_creates_smallint_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->smallInteger('rank');

        $this->assertSame(ColumnType::SmallInt, $column->type);
    }

    public function test_big_integer_creates_bigint_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->bigInteger('views');

        $this->assertSame(ColumnType::BigInt, $column->type);
    }

    public function test_float_creates_real_column(): void
    {
        $blueprint = new Blueprint('products');

        $column = $blueprint->float('weight');

        $this->assertSame(ColumnType::Real, $column->type);
    }

    public function test_decimal_creates_decimal_column_with_defaults(): void
    {
        $blueprint = new Blueprint('products');

        $column = $blueprint->decimal('price');

        $this->assertSame(ColumnType::Decimal, $column->type);
        $this->assertSame(['precision' => 8, 'scale' => 2], $column->options);
    }

    public function test_decimal_accepts_custom_precision_and_scale(): void
    {
        $blueprint = new Blueprint('products');

        $column = $blueprint->decimal('price', 12, 4);

        $this->assertSame(['precision' => 12, 'scale' => 4], $column->options);
    }

    public function test_boolean_creates_booltype_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->boolean('active');

        $this->assertSame(ColumnType::BoolType, $column->type);
    }

    public function test_json_creates_json_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->json('metadata');

        $this->assertSame(ColumnType::Json, $column->type);
    }

    public function test_uuid_creates_uuid_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->uuid('external_id');

        $this->assertSame(ColumnType::Uuid, $column->type);
    }

    public function test_date_creates_date_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->date('birthday');

        $this->assertSame(ColumnType::Date, $column->type);
    }

    public function test_time_creates_time_column(): void
    {
        $blueprint = new Blueprint('shifts');

        $column = $blueprint->time('start_time');

        $this->assertSame(ColumnType::Time, $column->type);
    }

    public function test_timestamp_creates_timestamp_column(): void
    {
        $blueprint = new Blueprint('users');

        $column = $blueprint->timestamp('verified_at');

        $this->assertSame(ColumnType::Timestamp, $column->type);
    }

    public function test_timestamps_adds_nullable_created_at_and_updated_at(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->timestamps();

        $this->assertCount(2, $blueprint->columns);
        $this->assertSame('created_at', $blueprint->columns[0]->name);
        $this->assertSame('updated_at', $blueprint->columns[1]->name);
        $this->assertTrue($blueprint->columns[0]->isNullable);
        $this->assertTrue($blueprint->columns[1]->isNullable);
    }

    public function test_columns_accumulate_in_order(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->id();
        $blueprint->string('email');
        $blueprint->boolean('active');

        $this->assertCount(3, $blueprint->columns);
        $this->assertSame('id', $blueprint->columns[0]->name);
        $this->assertSame('email', $blueprint->columns[1]->name);
        $this->assertSame('active', $blueprint->columns[2]->name);
    }

    public function test_column_factory_methods_return_column_instance(): void
    {
        $blueprint = new Blueprint('users');

        $this->assertInstanceOf(Column::class, $blueprint->string('name'));
    }

    public function test_foreign_creates_and_stores_foreign_key(): void
    {
        $blueprint = new Blueprint('posts');

        $fk = $blueprint->foreign('user_id');

        $this->assertInstanceOf(ForeignKey::class, $fk);
        $this->assertCount(1, $blueprint->foreignKeys);
        $this->assertSame($fk, $blueprint->foreignKeys[0]);
        $this->assertNull($fk->name);
    }

    public function test_foreign_accepts_custom_name(): void
    {
        $blueprint = new Blueprint('posts');

        $fk = $blueprint->foreign('user_id', 'fk_posts_user_id');

        $this->assertSame('fk_posts_user_id', $fk->name);
    }

    public function test_drop_column_stores_name_and_returns_self(): void
    {
        $blueprint = new Blueprint('users');

        $result = $blueprint->dropColumn('email');

        $this->assertSame($blueprint, $result);
        $this->assertSame(['email'], $blueprint->dropColumns);
    }

    public function test_drop_index_stores_name_and_returns_self(): void
    {
        $blueprint = new Blueprint('users');

        $result = $blueprint->dropIndex('users_email_unique');

        $this->assertSame($blueprint, $result);
        $this->assertSame(['users_email_unique'], $blueprint->dropIndexes);
    }

    public function test_drop_foreign_stores_name_and_returns_self(): void
    {
        $blueprint = new Blueprint('posts');

        $result = $blueprint->dropForeign('posts_user_id_foreign');

        $this->assertSame($blueprint, $result);
        $this->assertSame(['posts_user_id_foreign'], $blueprint->dropForeignKeys);
    }

    public function test_multiple_drop_calls_accumulate(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->dropColumn('first_name')->dropColumn('last_name');

        $this->assertSame(['first_name', 'last_name'], $blueprint->dropColumns);
    }

    public function test_unique_on_column_registers_index_on_blueprint(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->string('email')->unique();

        $this->assertCount(1, $blueprint->indexes);
        $this->assertSame(IndexType::Unique, $blueprint->indexes[0]->type);
        $this->assertSame('users', $blueprint->indexes[0]->table);
        $this->assertSame('email', $blueprint->indexes[0]->column);
        $this->assertNull($blueprint->indexes[0]->name);
    }

    public function test_unique_on_column_registers_index_with_custom_name(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->string('email')->unique('users_email_unique');

        $this->assertSame('users_email_unique', $blueprint->indexes[0]->name);
    }

    public function test_index_on_column_registers_index_on_blueprint(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->string('status')->index();

        $this->assertCount(1, $blueprint->indexes);
        $this->assertSame(IndexType::Index, $blueprint->indexes[0]->type);
        $this->assertSame('users', $blueprint->indexes[0]->table);
        $this->assertSame('status', $blueprint->indexes[0]->column);
        $this->assertNull($blueprint->indexes[0]->name);
    }

    public function test_multiple_column_indexes_accumulate_on_blueprint(): void
    {
        $blueprint = new Blueprint('users');

        $blueprint->string('email')->unique();
        $blueprint->string('status')->index();

        $this->assertCount(2, $blueprint->indexes);
        $this->assertSame(IndexType::Unique, $blueprint->indexes[0]->type);
        $this->assertSame(IndexType::Index, $blueprint->indexes[1]->type);
    }
}
