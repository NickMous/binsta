<?php

namespace NickMous\Binsta\Internals\Traits;

use DateTime;

trait HasTimestamps
{
    use HasCreatedAt;

    public ?DateTime $updatedAt = null;

    /**
     * Hydrate timestamp fields from bean
     */
    protected function hydrateTimestamps(): void
    {
        $this->hydrateCreatedAt();

        if ($this->bean === null) {
            return;
        }

        if (!empty($this->bean->updated_at)) {
            $this->updatedAt = new DateTime($this->bean->updated_at);
        }
    }

    /**
     * Prepare timestamp fields for saving
     */
    protected function prepareTimestamps(): void
    {
        $this->prepareCreatedAt();

        if ($this->bean === null) {
            return;
        }

        // Always update the updated_at timestamp
        $this->updatedAt = new DateTime();
        $this->bean->updated_at = $this->updatedAt->format('Y-m-d H:i:s');
    }

    /**
     * Get timestamp array for toArray() methods
     * @return array<string, string|null>
     */
    protected function getTimestampArray(): array
    {
        return array_merge(
            $this->getCreatedAtArray(),
            ['updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')]
        );
    }
}
