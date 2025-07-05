<?php

namespace NickMous\Binsta\Internals\Response;

class Response
{
    /**
     * Create a new Response instance.
     *
     * @param string $componentName The content of the response.
     * @param int $statusCode The HTTP status code for the response.
     * @param array<string, mixed> $headers An associative array of headers to include in the response.
     */
    public function __construct(
        public string $componentName = "" {
        set {
        // convert from PascalCase to kebab-case. Keep in mind that it could also have 3, // 4 or more words, like "HelloWorldComponent" or "HelloWorldComponentName".
        $this->componentName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
        }
        },
        public int $statusCode = 200,
        public array $headers = []
    ) {
    }
}
