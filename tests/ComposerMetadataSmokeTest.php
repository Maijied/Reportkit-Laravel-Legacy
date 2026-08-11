<?php

namespace ReportKit\Laravel\Legacy\Tests;

use PHPUnit\Framework\TestCase;

class ComposerMetadataSmokeTest extends TestCase
{
    public function testReportkitExtraDeclaresLaravelRange()
    {
        $path = dirname(__DIR__) . '/composer.json';
        $json = json_decode(file_get_contents($path), true);

        $this->assertIsArray($json);
        $this->assertSame('4.1 → 5.4', $json['extra']['reportkit']['laravel']['display']);
        $this->assertSame('reportkit/laravel-legacy', $json['name']);
    }
}
