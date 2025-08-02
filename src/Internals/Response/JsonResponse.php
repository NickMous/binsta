<?php

namespace NickMous\Binsta\Internals\Response;

class JsonResponse extends Response
{
    /**
     * @param array<mixed, mixed> $data
     * @param int $status
     * @param array<string, mixed> $headers
     * @throws \JsonException
     */
    public function __construct(
        public array $data,
        int $status = 200,
        array $headers = [],
    ) {
        $convertedData = json_encode($data, JSON_THROW_ON_ERROR);

        $headers['Content-Type'] = 'application/json';
        parent::__construct($convertedData, $status, $headers);
    }
}
