<?php

namespace NickMous\Binsta\Internals\Requests;

interface HasTransformation
{
    /**
     * Transform request data after it's loaded
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed> The transformed data
     */
    public function transform(array $data): array;
}
