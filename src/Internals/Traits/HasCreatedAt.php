<?php

namespace NickMous\Binsta\Internals\Traits;

use DateTime;

trait HasCreatedAt
{
    public ?DateTime $createdAt = null;

    /**
     * Hydrate created_at field from bean
     */
    protected function hydrateCreatedAt(): void
    {
        if ($this->bean === null) {
            return;
        }

        if (!empty($this->bean->created_at)) {
            $this->createdAt = new DateTime($this->bean->created_at);
        }
    }

    /**
     * Prepare created_at field for saving
     */
    protected function prepareCreatedAt(): void
    {
        if ($this->bean === null) {
            return;
        }

        // Set created_at if this is a new record
        if (!$this->exists() && $this->createdAt === null) {
            $this->createdAt = new DateTime();
        }

        // Set timestamp on bean
        if ($this->createdAt !== null) {
            $this->bean->created_at = $this->createdAt->format('Y-m-d H:i:s');
        }
    }

    /**
     * Get created_at for toArray() methods
     * @return array<string, string|null>
     */
    protected function getCreatedAtArray(): array
    {
        return [
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
