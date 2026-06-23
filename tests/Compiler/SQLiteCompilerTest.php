<?php

namespace Georgeff\Schema\Test\Compiler;

use Georgeff\Schema\Blueprint;
use Georgeff\Schema\Compiler\SQLiteCompiler;
use PHPUnit\Framework\TestCase;

final class SQLiteCompilerTest extends TestCase
{
    private SQLiteCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new SQLiteCompiler();
    }

    // -------------------------------------------------------------------------
    // drop
    // -------------------------------------------------------------------------

    public function test_drop_table(): void
    {
        $this->assertSame('DROP TABLE "users";', $this->compiler->drop('users'));
    }

    public function test_drop_table_if_exists(): void
    {
        $this->assertSame('DROP TABLE IF EXISTS "users";', $this->compiler->drop('users', true));
    }

    // -------------------------------------------------------------------------
    // create — column types
    // -------------------------------------------------------------------------

    public function test_create_with_bigint_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('views');

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('"views" INTEGER NOT NULL', $sql[0]);
    }

    public function test_create_with_integer_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('age');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"age" INTEGER NOT NULL', $sql[0]);
    }

    public function test_create_with_smallint_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->smallInteger('rank');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"rank" INTEGER NOT NULL', $sql[0]);
    }

    public function test_create_with_tinyint_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->tinyInteger('status');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"status" INTEGER NOT NULL', $sql[0]);
    }

    public function test_create_with_boolean_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('active');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"active" INTEGER NOT NULL', $sql[0]);
    }

    public function test_create_with_real_column(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->float('weight');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"weight" REAL NOT NULL', $sql[0]);
    }

    public function test_create_with_decimal_column(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->decimal('price');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"price" NUMERIC(8, 2) NOT NULL', $sql[0]);
    }

    public function test_create_with_decimal_custom_precision_and_scale(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->decimal('price', 12, 4);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"price" NUMERIC(12, 4) NOT NULL', $sql[0]);
    }

    public function test_create_with_varchar_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"email" VARCHAR(255) NOT NULL', $sql[0]);
    }

    public function test_create_with_varchar_custom_length(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('code', 10);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"code" VARCHAR(10) NOT NULL', $sql[0]);
    }

    public function test_create_with_char_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->char('initial');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"initial" VARCHAR(1) NOT NULL', $sql[0]);
    }

    public function test_create_with_text_column(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->text('body');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"body" TEXT NOT NULL', $sql[0]);
    }

    public function test_create_with_json_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->json('metadata');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"metadata" TEXT NOT NULL', $sql[0]);
    }

    public function test_create_with_uuid_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->uuid('external_id');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"external_id" TEXT NOT NULL', $sql[0]);
    }

    public function test_create_with_date_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->date('birthday');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"birthday" DATE NOT NULL', $sql[0]);
    }

    public function test_create_with_time_column(): void
    {
        $blueprint = new Blueprint('shifts');
        $blueprint->time('start_time');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"start_time" TIME NOT NULL', $sql[0]);
    }

    public function test_create_with_timestamp_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('verified_at');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"verified_at" TEXT NOT NULL', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // create — column modifiers
    // -------------------------------------------------------------------------

    public function test_create_nullable_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('deleted_at')->nullable();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"deleted_at" TEXT NULL', $sql[0]);
    }

    public function test_create_unsigned_is_silently_ignored(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('count')->unsigned();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringNotContainsString('UNSIGNED', $sql[0]);
    }

    public function test_create_column_with_string_default(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('status')->default('active');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString("DEFAULT 'active'", $sql[0]);
    }

    public function test_create_column_with_integer_default(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('count')->default(0);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('DEFAULT 0', $sql[0]);
    }

    public function test_create_column_with_boolean_true_default(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('active')->default(true);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('DEFAULT 1', $sql[0]);
    }

    public function test_create_column_with_boolean_false_default(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('active')->default(false);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('DEFAULT 0', $sql[0]);
    }

    public function test_create_column_with_null_default(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('deleted_at')->nullable()->default(null);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('DEFAULT NULL', $sql[0]);
    }

    public function test_create_column_with_raw_expression_default(): void
    {
        $blueprint = new Blueprint('events');
        $blueprint->timestamp('created_at')->defaultRaw('CURRENT_TIMESTAMP');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('DEFAULT CURRENT_TIMESTAMP', $sql[0]);
        $this->assertStringNotContainsString("'CURRENT_TIMESTAMP'", $sql[0]);
    }

    // -------------------------------------------------------------------------
    // create — id shorthand (INTEGER PRIMARY KEY AUTOINCREMENT)
    // -------------------------------------------------------------------------

    public function test_create_with_id_uses_integer_primary_key_autoincrement(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL', $sql[0]);
        $this->assertStringNotContainsString('CONSTRAINT', $sql[0]);
    }

    public function test_create_with_non_incrementing_primary_emits_table_constraint(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->uuid('id')->primary();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('PRIMARY KEY ("id")', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // create — indexes
    // -------------------------------------------------------------------------

    public function test_create_with_unique_index_is_separate_statement(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email')->unique();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(2, $sql);
        $this->assertSame('CREATE UNIQUE INDEX "users_email_unique" ON "users" ("email");', $sql[1]);
    }

    public function test_create_with_unique_index_custom_name(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email')->unique('my_unique_email');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"my_unique_email"', $sql[1]);
    }

    public function test_create_with_regular_index_is_separate_statement(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('status')->index();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(2, $sql);
        $this->assertSame('CREATE INDEX "users_status_index" ON "users" ("status");', $sql[1]);
    }

    public function test_create_with_multiple_indexes_returns_multiple_statements(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('email')->unique();
        $blueprint->string('status')->index();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(3, $sql);
    }

    // -------------------------------------------------------------------------
    // create — foreign keys
    // -------------------------------------------------------------------------

    public function test_create_with_foreign_key_inline(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id')->references('id')->on('users');

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE RESTRICT ON UPDATE RESTRICT', $sql[0]);
    }

    public function test_create_with_foreign_key_has_no_constraint_name(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id')->references('id')->on('users');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringNotContainsString('CONSTRAINT', $sql[0]);
    }

    public function test_create_with_foreign_key_custom_actions(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('SET NULL');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('ON DELETE CASCADE ON UPDATE SET NULL', $sql[0]);
    }

    public function test_create_foreign_key_throws_if_on_not_set(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id')->references('id');

        $this->expectException(\LogicException::class);

        $this->compiler->create($blueprint);
    }

    public function test_create_foreign_key_throws_if_references_not_set(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id')->on('users');

        $this->expectException(\LogicException::class);

        $this->compiler->create($blueprint);
    }

    // -------------------------------------------------------------------------
    // create — timestamps
    // -------------------------------------------------------------------------

    public function test_create_with_timestamps(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamps();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('"created_at" TEXT NULL', $sql[0]);
        $this->assertStringContainsString('"updated_at" TEXT NULL', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // alter — supported operations
    // -------------------------------------------------------------------------

    public function test_alter_add_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('phone');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE "users" ADD COLUMN "phone" VARCHAR(255) NOT NULL;', $sql);
    }

    public function test_alter_drop_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropColumn('phone');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE "users" DROP COLUMN "phone";', $sql);
    }

    public function test_alter_add_index(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('status')->index();

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('CREATE INDEX "users_status_index" ON "users" ("status");', $sql);
    }

    // -------------------------------------------------------------------------
    // alter — unsupported operations produce no statements
    // -------------------------------------------------------------------------

    public function test_alter_drop_index_produces_no_statement(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropIndex('users_email_unique');

        $sql = $this->compiler->alter($blueprint);

        $this->assertSame([], $sql);
    }

    public function test_alter_drop_foreign_produces_no_statement(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->dropForeign('posts_user_id_foreign');

        $sql = $this->compiler->alter($blueprint);

        $this->assertSame([], $sql);
    }

    public function test_alter_add_foreign_key_produces_no_statement(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->foreign('user_id')->references('id')->on('users');

        $sql = $this->compiler->alter($blueprint);

        $this->assertSame([], $sql);
    }

    public function test_table_exists_returns_query_string(): void
    {
        $sql = $this->compiler->tableExists();

        $this->assertStringContainsString('sqlite_master', $sql);
        $this->assertStringContainsString("'table'", $sql);
        $this->assertStringContainsString('?', $sql);
    }
}
