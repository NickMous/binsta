<?php

return [
    new class
    {
        /**
         * @return array<string, mixed>
         */
        public function returnData(): array
        {
            return [
                'path' => '/invalid-object',
                'closure' => function () {
                    return 'This is an invalid route object';
                }
            ];
        }
    }
];
