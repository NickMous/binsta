<?php

namespace NickMous\Binsta\Internals\Services;

class VueService
{
    public function render(string $component, array $props = []): string
    {
        $propsJson = json_encode($props, JSON_THROW_ON_ERROR);
        $viteResources = vite('main.ts');
        return <<<HTML
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
            <{$component} :props='{$propsJson}'></{$component}>
        </div>
        </body>
        </html>
        HTML;
    }
}