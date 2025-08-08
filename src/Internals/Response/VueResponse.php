<?php

namespace NickMous\Binsta\Internals\Response;

class VueResponse extends Response
{
    public function __construct(
        public string $componentName = "" {
        set {
        $this->componentName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
        }
        },
        /** @var array<string, mixed> */
        public array $props = [],
        int $statusCode = 200,
        array $headers = []
    ) {
        parent::__construct(statusCode: $statusCode, headers: $headers);
    }
}
