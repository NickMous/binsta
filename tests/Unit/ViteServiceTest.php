<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Services\ViteService;

it('generates the html boilerplate correctly', function (): void {
    $vueService = new ViteService();
    $vueResponse = $vueService->process(new VueResponse("HelloWorld"));

    expect($vueResponse)->toBeInstanceOf(VueResponse::class)
        ->and($vueResponse->content)->toContain('<div id="app">')
        ->and($vueResponse->content)->toContain('<hello-world>')
        ->and($vueResponse->content)->toContain('</hello-world>');
});

it('generates component with props', function (): void {
    $vueService = new ViteService();
    $props = ['user' => '123', 'title' => 'Test Page'];
    $vueResponse = $vueService->process(new VueResponse("HelloWorld", props: $props));

    expect($vueResponse)->toBeInstanceOf(VueResponse::class)
        ->and($vueResponse->content)->toContain('<div id="app">')
        ->and($vueResponse->content)->toContain('<hello-world user="123" title="Test Page">')
        ->and($vueResponse->content)->toContain('</hello-world>');
});

it('generates component without props when props are empty', function (): void {
    $vueService = new ViteService();
    $vueResponse = $vueService->process(new VueResponse("TestComponent", props: []));

    expect($vueResponse)->toBeInstanceOf(VueResponse::class)
        ->and($vueResponse->content)->toContain('<div id="app">')
        ->and($vueResponse->content)->toContain('<test-component>')
        ->and($vueResponse->content)->toContain('</test-component>');
});
