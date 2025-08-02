<?php

namespace NickMous\Binsta\Internals\Entities;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

abstract class Entity
{
    protected ?OODBBean $bean = null;
    protected bool $exists = false;

    public function __construct(?OODBBean $bean = null)
    {
        if ($bean !== null) {
            $this->bean = $bean;
            $this->exists = $bean->id > 0; // Only exists if it has a valid ID
            $this->hydrate();
        }
    }

    /**
     * Get the table name for this entity
     */
    abstract public static function getTableName(): string;

    /**
     * Get the entity ID (null if not yet saved)
     */
    public function getId(): ?int
    {
        if ($this->bean === null || !isset($this->bean->id) || $this->bean->id == 0) {
            return null;
        }

        return (int) $this->bean->id;
    }

    /**
     * Check if this entity exists in the database (has been saved)
     */
    public function exists(): bool
    {
        return $this->exists && $this->getId() !== null;
    }

    /**
     * Get the underlying RedBean bean
     */
    public function getBean(): ?OODBBean
    {
        return $this->bean;
    }

    /**
     * Set the underlying RedBean bean
     */
    public function setBean(OODBBean $bean): void
    {
        $this->bean = $bean;
        $this->exists = $bean->id > 0;
        $this->hydrate();
    }

    /**
     * Hydrate entity properties from the bean
     * Override in child classes to populate typed properties
     */
    protected function hydrate(): void
    {
        // Override in child classes
    }

    /**
     * Prepare the bean before saving
     * Override in child classes to set bean properties from typed properties
     */
    protected function prepare(): void
    {
        // Override in child classes
    }

    /**
     * Create a new bean instance for this entity
     */
    protected function createBean(): OODBBean
    {
        return R::dispense(static::getTableName());
    }

    /**
     * Save the entity to the database
     */
    public function save(): int
    {
        if ($this->bean === null) {
            $this->bean = $this->createBean();
        }

        $this->prepare();
        $id = R::store($this->bean);
        $this->exists = true;

        return $id;
    }

    /**
     * Delete the entity from the database
     */
    public function delete(): void
    {
        if ($this->bean !== null && $this->exists()) {
            R::trash($this->bean);
            $this->exists = false;
        }
    }

    /**
     * Refresh the entity from the database
     */
    public function refresh(): void
    {
        if ($this->exists() && $this->bean !== null) {
            $this->bean = R::load(static::getTableName(), $this->bean->id);
            $this->hydrate();
        }
    }
}
