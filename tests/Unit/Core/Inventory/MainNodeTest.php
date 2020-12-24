<?php

namespace Maestro\Tests\Unit\Core\Inventory;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Inventory\RepositoryNodes;
use PHPUnit\Framework\TestCase;

class MainNodeTest extends TestCase
{
    public function testForTags(): void
    {
        $mainNode = MainNode::fromArray([
            'repositories' => [
                [
                    'name' => 'foobar',
                    'url' => 'example',
                    'tags' => [ 'lib' ],
                ],
                [
                    'name' => 'barfoo',
                    'url' => 'example',
                    'tags' => [ 'extension' ],
                ],
                [
                    'name' => 'bazgoo',
                    'url' => 'example',
                    'tags' => [ 'ball', 'extension' ],
                ],
            ],
        ]);

        $nodes = $mainNode->repositories()->forTags([]);
        self::assertCount(3, $nodes, 'Returns all for empty tags');

        $nodes = $mainNode->repositories()->forTags(['extension']);
        self::assertCount(2, $nodes, 'Returns intersection of tags');

        $nodes = $mainNode->repositories()->forTags(['extension', 'foobar']);
        self::assertCount(2, $nodes);
    }
}
