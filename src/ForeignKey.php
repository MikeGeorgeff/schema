<?php

namespace Georgeff\Schema;

final class ForeignKey
{
    private const array REF_ACTIONS = ['NO ACTION', 'RESTRICT', 'SET NULL', 'CASCADE'];

    public private(set) ?string $references = null;

    public private(set) ?string $on = null;

    public private(set) ?string $onDelete = 'RESTRICT';

    public private(set) ?string $onUpdate = 'RESTRICT';

    public function __construct(
        public readonly string $column,
        public readonly ?string $name = null
    ) {}

    public function references(string $column): self
    {
        $this->references = $column;

        return $this;
    }

    public function on(string $table): self
    {
        $this->on = $table;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function onDelete(string $action): self
    {
        $action = strtoupper($action);

        if (!in_array($action, self::REF_ACTIONS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid action [%s] for onDelete. Valid actions: %s',
                $action,
                implode(', ', self::REF_ACTIONS)
            ));
        }

        $this->onDelete = $action;

        return $this;
    }

    public function onUpdate(string $action): self
    {
        $action = strtoupper($action);

        if (!in_array($action, self::REF_ACTIONS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid action [%s] for onUpdate. Valid actions: %s',
                $action,
                implode(', ', self::REF_ACTIONS)
            ));
        }

        $this->onUpdate = $action;

        return $this;
    }
}
