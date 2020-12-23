<?php

namespace Maestro\Tests\Unit\Composer;

use Closure;
use Generator;
use Maestro\Composer\ComposerVersion;
use PHPUnit\Framework\TestCase;

class ComposerVersionTest extends TestCase
{
    /**
     * @dataProvider provideVersion
     */
    public function testVersion(string $version, Closure $assertion): void
    {
        $version = new ComposerVersion($version);
        $assertion($version);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideVersion(): Generator
    {
        yield [
            '1.0',
            fn (ComposerVersion $version) => self::assertEquals('1.0', $version->version()),
        ];
        yield 'greater than true' => [
            '1.1',
            fn (ComposerVersion $version) => self::assertTrue($version->greaterThan('1.0')),
        ];
        yield 'equal to' => [
            '~1.0',
            fn (ComposerVersion $version) => self::assertTrue($version->equalTo('~1.0')),
        ];
    }
}
