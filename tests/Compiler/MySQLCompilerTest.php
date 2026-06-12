<?php

namespace Georgeff\Schema\Test\Compiler;

use Georgeff\Schema\Blueprint;
use Georgeff\Schema\Compiler\MySQLCompiler;
use PHPUnit\Framework\TestCase;

final class MySQLCompilerTest extends TestCase
{
    private MySQLCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new MySQLCompiler();
    }

    // -------------------------------------------------------------------------
    // drop
    // -------------------------------------------------------------------------

    public function test_drop_table(): void
    {
        $this->assertSame('DROP TABLE `users`;', $this->compiler->drop('users'));
    }

    public function test_drop_table_if_exists(): void
    {
        $this->assertSame('DROP TABLE IF EXISTS `users`;', $this->compiler->drop('users', true));
    }

    // -------------------------------------------------------------------------
    // create — table options
    // -------------------------------------------------------------------------

    public function test_create_includes_default_engine_charset_collate(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', $sql[0]);
    }

    public function test_create_uses_custom_engine_charset_collate(): void
    {
        $compiler = new MySQLCompiler('MyISAM', 'utf8', 'utf8_general_ci');

        $blueprint = new Blueprint('users');
        $blueprint->id();

        $sql = $compiler->create($blueprint);

        $this->assertStringContainsString('ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci', $sql[0]);
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
        $this->assertStringContainsString('`views` BIGINT NOT NULL', $sql[0]);
    }

    public function test_create_with_integer_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('age');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`age` INT NOT NULL', $sql[0]);
    }

    public function test_create_with_smallint_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->smallInteger('rank');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`rank` SMALLINT NOT NULL', $sql[0]);
    }

    public function test_create_with_tinyint_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->tinyInteger('status');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`status` TINYINT NOT NULL', $sql[0]);
    }

    public function test_create_with_real_column(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->float('weight');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`weight` FLOAT NOT NULL', $sql[0]);
    }

    public function test_create_with_decimal_column(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->decimal('price');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`price` DECIMAL(8, 2) NOT NULL', $sql[0]);
    }

    public function test_create_with_decimal_custom_precision_and_scale(): void
    {
        $blueprint = new Blueprint('products');
        $blueprint->decimal('price', 12, 4);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`price` DECIMAL(12, 4) NOT NULL', $sql[0]);
    }

    public function test_create_with_varchar_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`email` VARCHAR(255) NOT NULL', $sql[0]);
    }

    public function test_create_with_varchar_custom_length(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('code', 10);

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`code` VARCHAR(10) NOT NULL', $sql[0]);
    }

    public function test_create_with_char_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->char('initial');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`initial` CHAR(1) NOT NULL', $sql[0]);
    }

    public function test_create_with_text_column(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->text('body');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`body` TEXT NOT NULL', $sql[0]);
    }

    public function test_create_with_boolean_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('active');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`active` TINYINT(1) NOT NULL', $sql[0]);
    }

    public function test_create_with_json_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->json('metadata');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`metadata` JSON NOT NULL', $sql[0]);
    }

    public function test_create_with_uuid_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->uuid('external_id');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`external_id` CHAR(36) NOT NULL', $sql[0]);
    }

    public function test_create_with_date_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->date('birthday');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`birthday` DATE NOT NULL', $sql[0]);
    }

    public function test_create_with_time_column(): void
    {
        $blueprint = new Blueprint('shifts');
        $blueprint->time('start_time');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`start_time` TIME NOT NULL', $sql[0]);
    }

    public function test_create_with_timestamp_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('verified_at');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`verified_at` TIMESTAMP NOT NULL', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // create — column modifiers
    // -------------------------------------------------------------------------

    public function test_create_nullable_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('deleted_at')->nullable();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`deleted_at` TIMESTAMP NULL', $sql[0]);
    }

    public function test_create_unsigned_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('count')->unsigned();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`count` BIGINT UNSIGNED NOT NULL', $sql[0]);
    }

    public function test_create_incrementing_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('id')->incrementing();

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`id` BIGINT NOT NULL AUTO_INCREMENT', $sql[0]);
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

    // -------------------------------------------------------------------------
    // create — id shorthand
    // -------------------------------------------------------------------------

    public function test_create_with_id(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT', $sql[0]);
        $this->assertStringContainsString('PRIMARY KEY (`id`)', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // create — indexes inline
    // -------------------------------------------------------------------------

    public function test_create_with_primary_key_inline(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('id')->primary();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('PRIMARY KEY (`id`)', $sql[0]);
    }

    public function test_create_with_unique_key_inline(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email')->unique();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('UNIQUE KEY `users_email_unique` (`email`)', $sql[0]);
    }

    public function test_create_with_unique_key_custom_name(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email')->unique('my_unique_email');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`my_unique_email`', $sql[0]);
    }

    public function test_create_with_regular_key_inline(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('status')->index();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
        $this->assertStringContainsString('KEY `users_status_index` (`status`)', $sql[0]);
    }

    public function test_create_always_returns_single_statement(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('email')->unique();
        $blueprint->string('status')->index();

        $sql = $this->compiler->create($blueprint);

        $this->assertCount(1, $sql);
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
        $this->assertStringContainsString('CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT', $sql[0]);
    }

    public function test_create_with_foreign_key_custom_name(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->bigInteger('user_id');
        $blueprint->foreign('user_id', 'fk_posts_user')->references('id')->on('users');

        $sql = $this->compiler->create($blueprint);

        $this->assertStringContainsString('`fk_posts_user`', $sql[0]);
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

        $this->assertStringContainsString('`created_at` TIMESTAMP NULL', $sql[0]);
        $this->assertStringContainsString('`updated_at` TIMESTAMP NULL', $sql[0]);
    }

    // -------------------------------------------------------------------------
    // alter
    // -------------------------------------------------------------------------

    public function test_alter_add_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('phone');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(255) NOT NULL;', $sql);
    }

    public function test_alter_drop_column(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropColumn('phone');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `users` DROP COLUMN `phone`;', $sql);
    }

    public function test_alter_drop_index(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropIndex('users_email_unique');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `users` DROP INDEX `users_email_unique`;', $sql);
    }

    public function test_alter_drop_foreign(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->dropForeign('posts_user_id_foreign');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `posts` DROP FOREIGN KEY `posts_user_id_foreign`;', $sql);
    }

    public function test_alter_add_index(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('status')->index();

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `users` ADD KEY `users_status_index` (`status`);', $sql);
    }

    public function test_alter_add_foreign_key(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->foreign('user_id')->references('id')->on('users');

        $sql = $this->compiler->alter($blueprint);

        $this->assertContains('ALTER TABLE `posts` ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;', $sql);
    }
}
