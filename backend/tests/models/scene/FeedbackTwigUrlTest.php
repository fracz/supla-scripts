<?php

namespace suplascripts\tests\models\scene;

use FileSystemCache;
use PHPUnit\Framework\TestCase;
use suplascripts\models\scene\FeedbackInterpolator;

class FeedbackTwigUrlTest extends TestCase {

    private $directory;
    private $previousCacheDirectory;
    private $url;

    protected function setUp(): void {
        $this->directory = sys_get_temp_dir() . '/supla-twig-url-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        $this->previousCacheDirectory = FileSystemCache::$cacheDir;
        FileSystemCache::$cacheDir = $this->directory . '/cache';
        file_put_contents($this->directory . '/response.txt', 'Temperature=21.5; Humidity=45');
        $this->url = 'file://' . $this->directory . '/response.txt';
    }

    protected function tearDown(): void {
        FileSystemCache::$cacheDir = $this->previousCacheDirectory;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->directory);
    }

    public function testFetchesUrlThroughRealTwigFunctionAndCurl() {
        $template = '{{ getUrl("' . $this->url . '") }}';

        $this->assertSame('Temperature=21.5; Humidity=45', (new FeedbackInterpolator())->interpolate($template));
    }

    public function testReturnsCachedResponseWhenSourceIsNoLongerAvailable() {
        $template = '{{ getUrl("' . $this->url . '") }}';
        $interpolator = new FeedbackInterpolator();
        $this->assertSame('Temperature=21.5; Humidity=45', $interpolator->interpolate($template));

        unlink($this->directory . '/response.txt');

        $this->assertSame('Temperature=21.5; Humidity=45', $interpolator->interpolate($template));
    }

    /** @dataProvider regexArguments */
    public function testExtractsResponseValuesUsingTwigArguments($arguments, $expected) {
        $template = '{{ getUrl("' . $this->url . '", ' . $arguments . ') }}';

        $this->assertSame($expected, (new FeedbackInterpolator())->interpolate($template));
    }

    public function regexArguments() {
        return [
            'positional regex uses first group' => ['"/Temperature=([0-9.]+)/"', '21.5'],
            'configuration as second argument' => ['{"regex": "/Humidity=([0-9]+)/"}', '45'],
            'explicit capture group' => [
                '"/(Temperature)=([0-9.]+)/", {"regexGroup": 2}', '21.5',
            ],
            'capture group zero returns full match' => [
                '"/Temperature=([0-9.]+)/", {"regexGroup": 0}', 'Temperature=21.5',
            ],
            'nonmatching regex retains original response' => [
                '"/Pressure=([0-9]+)/"', 'Temperature=21.5; Humidity=45',
            ],
        ];
    }

    public function testDifferentRegexConfigurationsDoNotShareCachedResults() {
        $interpolator = new FeedbackInterpolator();
        $this->assertSame('21.5', $interpolator->interpolate(
            '{{ getUrl("' . $this->url . '", "/Temperature=([0-9.]+)/") }}'
        ));
        file_put_contents($this->directory . '/response.txt', 'Temperature=23.0; Humidity=50');

        $this->assertSame('50', $interpolator->interpolate(
            '{{ getUrl("' . $this->url . '", "/Humidity=([0-9]+)/") }}'
        ));
        $this->assertSame('21.5', $interpolator->interpolate(
            '{{ getUrl("' . $this->url . '", "/Temperature=([0-9.]+)/") }}'
        ));
    }

    public function testFailedFetchRendersErrorMarker() {
        unlink($this->directory . '/response.txt');
        $template = '{{ getUrl("' . $this->url . '") }}';

        $this->assertSame('URL_FETCH_ERROR', (new FeedbackInterpolator())->interpolate($template));
    }
}
