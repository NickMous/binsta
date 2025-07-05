<?php

namespace NickMous\Binsta\Internals\Response;

class Response
{
    /**
     * Create a new Response instance.
     *
     * @param string $content The content of the response.
     * @param int $statusCode The HTTP status code for the response.
     * @param array<string, mixed> $headers An associative array of headers to include in the response.
     */
    public function __construct(
        public string $content = "",
        public int $statusCode = 200,
        public array $headers = []
    ) {
    }
}
