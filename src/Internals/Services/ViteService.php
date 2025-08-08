<?php

namespace NickMous\Binsta\Internals\Services;

use JsonException;
use NickMous\Binsta\Internals\Response\VueResponse;

class ViteService
{
    private const string MANIFEST_FILE = __DIR__ . '/../../../public/dist/.vite/manifest.json';
    private const string DEV_MANIFEST_FILE = __DIR__ . '/../../../public/dist/.vite/dev-manifest.json';
    public function process(VueResponse $vueResponse): VueResponse
    {
        $attributes = $this->buildComponentAttributes($vueResponse->props);

        $content = <<<HTML
        <!doctype html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
             <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
             <meta http-equiv="X-UA-Compatible" content="ie=edge">
             <title>Binsta</title>
             {$this->generateTags('main.ts')}
        </head>
        <body>
        <div id="app">
            <{$vueResponse->componentName}{$attributes}>
            </{$vueResponse->componentName}>
        </div>
        </body>
        </html>
        HTML;

        $vueResponse->content = $content;

        return $vueResponse;
    }

    /**
     * @param array<string, mixed> $props
     */
    private function buildComponentAttributes(array $props): string
    {
        if (empty($props)) {
            return '';
        }

        $attributes = [];
        foreach ($props as $key => $value) {
            $attributes[] = $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
        }

        return ' ' . implode(' ', $attributes);
    }

    // @codeCoverageIgnoreStart
    private function generateTags(string $entry): string
    {
        $tags = array_merge(
            $this->jsTag($entry),
            $this->jsPreloadImports($entry),
            $this->cssTag($entry)
        );

        return implode("\n", $tags);
    }

    /**
     * @param string $entry
     * @return array<string>
     * @throws JsonException
     */
    private function jsTag(string $entry): array
    {
        $isViteDevRunning = $this->isViteDevRunning($entry);

        if (!$isViteDevRunning) {
            $assetUrl = $this->getAssetUrl($entry);

            if (empty($assetUrl)) {
                return [];
            }

            return ['<script type="module" src="' . $assetUrl . '"></script>'];
        }

        $devManifest = $this->getManifest(self::DEV_MANIFEST_FILE);
        $devUrl = $devManifest["devServer"]["url"];

        if (empty($devUrl)) {
            return [];
        }

        return [
            '<script type="module" src="' . $devUrl . '/' . $entry . '"></script>',
            '<script type="module" src="' . $devUrl . '/@vite/client"></script>'
        ];
    }

    private function isViteDevRunning(string $entry): bool
    {
        $manifestExists = file_exists(self::DEV_MANIFEST_FILE);

        if (!$manifestExists) {
            return false;
        }

        $manifest = $this->getManifest(self::DEV_MANIFEST_FILE);

        return isset($manifest[$entry]['file']);
    }

    /**
     * @return array<string, array<string, string[]|string|int|bool>|string>
     * @throws JsonException
     */
    private function getManifest(string $manifestFilePath): array
    {
        if (!file_exists($manifestFilePath)) {
            return [];
        }

        $content = file_get_contents($manifestFilePath);
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    private function getAssetUrl(string $entry): ?string
    {
        $manifest = $this->getManifest(self::MANIFEST_FILE);

        return isset($manifest[$entry])
            ? '/dist/' . $manifest[$entry]['file']
            : null;
    }

    /**
     * @param string $entry
     * @return array<string>
     * @throws JsonException
     */
    private function jsPreloadImports(string $entry): array
    {
        if ($this->isViteDevRunning($entry)) {
            return [];
        }

        $preloadTags = [];
        $importUrls = $this->getImportUrls($entry);

        foreach ($importUrls as $url) {
            $preloadTags[] = '<link rel="modulepreload" href="' . $url . '">';
        }

        return $preloadTags;
    }

    /**
     * @param string $entry
     * @return array<string>
     * @throws JsonException
     */
    private function getImportUrls(string $entry): array
    {
        $manifest = $this->getManifest(self::MANIFEST_FILE);

        if (!isset($manifest[$entry]['imports'])) {
            return [];
        }

        $urls = [];
        foreach ($manifest[$entry]['imports'] as $import) {
            if (isset($manifest[$import]['file'])) {
                $urls[] = '/dist/' . $manifest[$import]['file'];
            }
        }

        return $urls;
    }

    /**
     * @param string $entry
     * @return array<string>
     * @throws JsonException
     */
    private function cssTag(string $entry): array
    {
        if ($this->isViteDevRunning($entry)) {
            return [];
        }

        $cssUrls = $this->getCssUrls($entry);
        $tags = [];

        foreach ($cssUrls as $url) {
            $tags[] = '<link rel="stylesheet" href="' . $url . '">';
        }

        return $tags;
    }

    /**
     * @return array<string>
     * @throws JsonException
     */
    private function getCssUrls(string $entry): array
    {
        $manifest = $this->getManifest(self::MANIFEST_FILE);

        if (!isset($manifest[$entry]['css'])) {
            return [];
        }

        $urls = [];
        foreach ($manifest[$entry]['css'] as $cssFile) {
            $urls[] = '/dist/' . $cssFile;
        }

        return $urls;
    }
    // @codeCoverageIgnoreEnd
}
