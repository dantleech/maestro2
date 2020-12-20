<?php

namespace Maestro2\Tests\Unit\Core\Inventory;

use Closure;
use Generator;
use Maestro2\Core\Inventory\InventoryLoader;
use Maestro2\Core\Inventory\MainNode;
use Maestro2\Tests\IntegrationTestCase;

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
