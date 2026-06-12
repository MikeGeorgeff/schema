<?php

namespace Georgeff\Schema;

final class Blueprint
{
    /**
     * @var Column[]
     */
    public private(set) array $columns = [];

    /**
     * @var Index[]
     */
    public private(set) array $indexes = [];

    /**
     * @var ForeignKey[]
     */
    public private(set) array $foreignKeys = [];

    /**
     * @var string[]
     */
    public private(set) array $dropColumns = [];

    /**
     * @var string[]
     */
    public private(set) array $dropIndexes = [];

    /**
     * @var string[]
     */
    public private(set) array $dropForeignKeys = [];

    public function __construct(public readonly string $table) {}

    public function id(string $name = 'id'): Column
    {
        return $this->column($name, ColumnType::BigInt)
                    ->primary()
                    ->unsigned()
                    ->incrementing();
    }

    public function string(string $name, int $length = 255): Column
    {
        return $this->column($name, ColumnType::Varchar)->addOption('length', $length);
    }

    public function char(string $name, int $length = 1): Column
    {
        return $this->column($name, ColumnType::Char)->addOption('length', $length);
    }

    public function text(string $name): Column
    {
        return $this->column($name, ColumnType::Text);
    }

    public function integer(string $name): Column
    {
        return $this->column($name, ColumnType::RegInt);
    }

    public function tinyInteger(string $name): Column
    {
        return $this->column($name, ColumnType::TinyInt);
    }

    public function smallInteger(string $name): Column
    {
        return $this->column($name, ColumnType::SmallInt);
    }

    public function bigInteger(string $name): Column
    {
        return $this->column($name, ColumnType::BigInt);
    }

    public function float(string $name): Column
    {
        return $this->column($name, ColumnType::Real);
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): Column
    {
        return $this->column($name, ColumnType::Decimal)
                    ->addOption('precision', $precision)
                    ->addOption('scale', $scale);
    }

    public function boolean(string $name): Column
    {
        return $this->column($name, ColumnType::BoolType);
    }

    public function json(string $name): Column
    {
        return $this->column($name, ColumnType::Json);
    }

    public function uuid(string $name): Column
    {
        return $this->column($name, ColumnType::Uuid);
    }

    public function date(string $name): Column
    {
        return $this->column($name, ColumnType::Date);
    }

    public function time(string $name): Column
    {
        return $this->column($name, ColumnType::Time);
    }

    public function timestamp(string $name): Column
    {
        return $this->column($name, ColumnType::Timestamp);
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    public function foreign(string $column, ?string $name = null): ForeignKey
    {
        return $this->foreignKeys[] = new ForeignKey($column, $name);
    }

    public function dropColumn(string $name): self
    {
        $this->dropColumns[] = $name;

        return $this;
    }

    public function dropIndex(string $name): self
    {
        $this->dropIndexes[] = $name;

        return $this;
    }

    public function dropForeign(string $name): self
    {
        $this->dropForeignKeys[] = $name;

        return $this;
    }

    private function column(string $name, ColumnType $type): Column
    {
        return $this->columns[] = new Column(
            $name,
            $type,
            function (IndexType $indexType, string $column, ?string $indexName = null) {
                $this->index($indexType, $column, $indexName);
            }
        );
    }

    private function index(IndexType $type, string $column, ?string $name = null): void
    {
        $this->indexes[] = new Index($type, $this->table, $column, $name);
    }
}
