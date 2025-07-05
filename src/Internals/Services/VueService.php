<?php

namespace NickMous\Binsta\Internals\Services;

use NickMous\Binsta\Internals\Response\VueResponse;

class VueService
{
    public function process(VueResponse $vueResponse): VueResponse
    {
        $viteResources = vite('main.ts');
        $content = <<<HTML
        <!doctype html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
             <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
             <meta http-equiv="X-UA-Compatible" content="ie=edge">
             <title>Binsta</title>
             {$viteResources}
        </head>
        <body>
        <div id="app">
            <{$vueResponse->componentName}></{$vueResponse->componentName}>
        </div>
        </body>
        </html>
        HTML;

        $vueResponse->content = $content;

        return $vueResponse;
    }
}
