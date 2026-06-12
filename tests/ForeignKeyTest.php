<?php

namespace Georgeff\Schema\Test;

use Georgeff\Schema\ForeignKey;
use PHPUnit\Framework\TestCase;

final class ForeignKeyTest extends TestCase
{
    public function test_constructor_sets_column(): void
    {
        $fk = new ForeignKey('user_id');

        $this->assertSame('user_id', $fk->column);
    }

    public function test_constructor_sets_optional_name(): void
    {
        $fk = new ForeignKey('user_id', 'fk_posts_user_id');

        $this->assertSame('fk_posts_user_id', $fk->name);
    }

    public function test_defaults(): void
    {
        $fk = new ForeignKey('user_id');

        $this->assertNull($fk->name);
        $this->assertNull($fk->references);
        $this->assertNull($fk->on);
        $this->assertSame('RESTRICT', $fk->onDelete);
        $this->assertSame('RESTRICT', $fk->onUpdate);
    }

    public function test_references_sets_column_and_returns_self(): void
    {
        $fk = new ForeignKey('user_id');

        $result = $fk->references('id');

        $this->assertSame($fk, $result);
        $this->assertSame('id', $fk->references);
    }

    public function test_on_sets_table_and_returns_self(): void
    {
        $fk = new ForeignKey('user_id');

        $result = $fk->on('users');

        $this->assertSame($fk, $result);
        $this->assertSame('users', $fk->on);
    }

    public function test_on_delete_sets_action_and_returns_self(): void
    {
        $fk = new ForeignKey('user_id');

        $result = $fk->onDelete('CASCADE');

        $this->assertSame($fk, $result);
        $this->assertSame('CASCADE', $fk->onDelete);
    }

    public function test_on_delete_normalises_to_uppercase(): void
    {
        $fk = new ForeignKey('user_id');

        $fk->onDelete('cascade');

        $this->assertSame('CASCADE', $fk->onDelete);
    }

    public function test_on_delete_accepts_all_valid_actions(): void
    {
        foreach (['NO ACTION', 'RESTRICT', 'SET NULL', 'CASCADE'] as $action) {
            $fk = new ForeignKey('user_id');
            $fk->onDelete($action);
            $this->assertSame($action, $fk->onDelete);
        }
    }

    public function test_on_delete_throws_for_invalid_action(): void
    {
        $fk = new ForeignKey('user_id');

        $this->expectException(\InvalidArgumentException::class);

        $fk->onDelete('EXPLODE');
    }

    public function test_on_update_sets_action_and_returns_self(): void
    {
        $fk = new ForeignKey('user_id');

        $result = $fk->onUpdate('CASCADE');

        $this->assertSame($fk, $result);
        $this->assertSame('CASCADE', $fk->onUpdate);
    }

    public function test_on_update_normalises_to_uppercase(): void
    {
        $fk = new ForeignKey('user_id');

        $fk->onUpdate('set null');

        $this->assertSame('SET NULL', $fk->onUpdate);
    }

    public function test_on_update_accepts_all_valid_actions(): void
    {
        foreach (['NO ACTION', 'RESTRICT', 'SET NULL', 'CASCADE'] as $action) {
            $fk = new ForeignKey('user_id');
            $fk->onUpdate($action);
            $this->assertSame($action, $fk->onUpdate);
        }
    }

    public function test_on_update_throws_for_invalid_action(): void
    {
        $fk = new ForeignKey('user_id');

        $this->expectException(\InvalidArgumentException::class);

        $fk->onUpdate('EXPLODE');
    }

    public function test_builder_is_chainable(): void
    {
        $fk = (new ForeignKey('user_id'))
            ->references('id')
            ->on('users')
            ->onDelete('CASCADE')
            ->onUpdate('SET NULL');

        $this->assertSame('id', $fk->references);
        $this->assertSame('users', $fk->on);
        $this->assertSame('CASCADE', $fk->onDelete);
        $this->assertSame('SET NULL', $fk->onUpdate);
    }
}
