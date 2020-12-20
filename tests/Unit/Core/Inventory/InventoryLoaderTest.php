<?php

namespace Maestro\Tests\Unit\Core\Inventory;

use Closure;
use Generator;
use Maestro\Core\Inventory\InventoryLoader;
use Maestro\Core\Inventory\MainNode;
use Maestro\Tests\IntegrationTestCase;

class InventoryLoaderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideInventories
     */
    public function testLoadsInventories(array $inventories, Closure $assertion): void
    {
        $paths = [];
        foreach ($inventories as $index => $inventory) {
            $name = sprintf(
                'inventory%s.json',
                $index
            );
            $this->workspace()->put($name, json_encode($inventory));
            $paths[] = $this->workspace()->path($name);
        }

        $mainNode = (new InventoryLoader($paths))->load();
        $assertion($mainNode);
    }

    public function provideInventories(): Generator
    {
        yield 'minimum' => [
            [
                [
                    'repositories' => [],
                ],
            ],
            function (MainNode $node) {
                self::assertInstanceOf(MainNode::class, $node);
            }
        ];

        yield 'merge vars' => [
            [
                [
                    'repositories' => [],
                    'vars' => [
                        'one' => 'two',
                    ],
                ],
                [
                    'vars' => [
                        'three' => 'four',
                    ],
                ],
            ],
            function (MainNode $node) {
                self::assertEquals([
                    'one' => 'two',
                    'three' => 'four',
                ], $node->vars()->toArray());
            }
        ];
    }
}
