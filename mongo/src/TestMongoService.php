<?php


namespace App;

use MongoDB\BSON\ObjectId;
use MongoDB\Client;

class TestMongoService
{
    protected array $mongoIds = ['5a2493c33c95a1281836eb6a', '5a2493c33c95a1281836eb6b', '5a2493c33c95a1281836eb6c'];

    /** @var Client */
    protected Client $mongoClient;

    public function __construct()
    {
        // En pratique déclarer le client en tant que service et utiliser l'injection
        $this->mongoClient = new Client;
    }

    public function emptyCollection(): void
    {
        $collection = $this->mongoClient->test->discount;
        $collection->drop();
    }

    public function creationObjects(): void
    {
        $collection = $this->mongoClient->test->discount;

        $collection->insertMany([
            [
                '_id' => new ObjectId($this->mongoIds[0]),
                'value' => '15%',
                'target' => 'product',
                'start' => time(),
                'end'  => time() + 1800,
                'conditions' => []
            ],
            [
                '_id' => new ObjectId($this->mongoIds[1]),
                'value' => '25%',
                'target' => 'product',
                'start' => time(),
                'end'  => time() + 3600,
                'conditions' => []
            ],
            [
                '_id' => new ObjectId($this->mongoIds[2]),
                'value' => '1.75%',
                'target' => 'product',
                'start' => time(),
                'end'  => time() + 4400,
                'conditions' => []
            ],
        ]);
    }

    public function updateConditions(): void
    {
        $collection = $this->mongoClient->test->discount;

        $collection->updateOne(
            ['_id' => new ObjectId($this->mongoIds[0])],
            [
                '$set' => ['conditions' =>
                    [
                        [
                            'target' => 'category',
                            'value' => 2,
                            'conditions' => [['target' => 'color', 'value' => 'red'], ['target' => 'color', 'value' => 'blue']],
                        ],
                        [
                            'target' => 'category',
                            'value' => 1,
                        ]
                    ]
                ]
            ]
        );
        $collection->updateOne(
            ['_id' => new ObjectId($this->mongoIds[1])],
            [
                '$set' => ['conditions' =>
                    [
                        [
                            'target' => 'color',
                            'value' => 'green',
                        ],
                        [
                            'target' => 'category',
                            'value' => 2,
                        ]
                    ]
                ]
            ]
        );
        $collection->updateOne(
            ['_id' => new ObjectId($this->mongoIds[2])],
            [
                '$set' => ['conditions' =>
                    [
                        [
                            'target' => 'color',
                            'value' => 'green',
                        ],
                    ]
                ]
            ]
        );
    }

    public function parseDiscountsRecursive(array $product, Object $conditions): bool
    {
        foreach ($conditions as $condition) {

            if ($condition['value'] != $product[$condition['target']]) {
                // If the condition is not validated, we don't need to go deeper into recursivity
                // So we test the next condition
                continue;
            } else {
                if (true === isset($condition['conditions'])) {
                    // The condition is validated, but there are more conditions to test
                    // (the condition has condition(s))
                    return $this->parseDiscountsRecursive($product, $condition['conditions']);
                } else {
                    // The condition is validated, and there is no more condition = we can return true
                    return true;
                }
            }
        }

        // There is no validated condition, we return false
        return false;
    }

    // List discounts for a product
    public function parseDiscounts(array $product): void
    {
        $collection = $this->mongoClient->test->discount;
        $cursor = $collection->find();

        foreach ($cursor as $discount) {
            $conditions = $discount['conditions'];

            if (true === $this->parseDiscountsRecursive($product, $conditions)) {
                echo 'Discount found: ' . $discount['_id'] . PHP_EOL;

                // Affiche
                // Discount found: 5a2493c33c95a1281836eb6a
                // Discount found: 5a2493c33c95a1281836eb6b
            }
        }
    }
}