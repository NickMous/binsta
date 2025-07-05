<?php

use NickMous\Binsta\Internals\Response\VueResponse;
use NickMous\Binsta\Internals\Services\VueService;

it('generates the html boilerplate correctly', function () {
    $vueService = new VueService();
    $vueResponse = $vueService->process(new VueResponse("HelloWorld"));

    expect($vueResponse)->toBeInstanceOf(VueResponse::class)
        ->and($vueResponse->content)->toContain('<div id="app">')
        ->and($vueResponse->content)->toContain('<hello-world></hello-world>');
});
