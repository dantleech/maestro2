<?php

namespace Maestro2\Tests\Unit\Core\Path;

use Generator;
use Maestro2\Core\Path\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * @dataProvider providePath
     */
    public function testPath(string $root, string $path, ?string $cwd, string $expected): void
    {
        self::assertEquals($expected, (new Path($root, $cwd))->resolve($path));
    }

    /**
     * @return Generator<mixed>
     */
    public function providePath(): Generator
    {
        yield [
            '/root',
            '/path/to/foo',
            '/path',
            '/root/path/to/foo',
        ];

        yield [
            '/root',
            'to/foo',
            '/path',
            '/root/path/to/foo',
        ];


        yield [
            '/root',
            '',
            '/path',
            '/root/path',
        ];

        yield [
            '/root',
            '',
            '',
            '/root',
        ];
    }
}
