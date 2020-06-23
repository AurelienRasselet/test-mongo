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

    // List discounts for a product
    public function parseDiscounts(array $product): void
    {
        $collection = $this->mongoClient->test->discount;
        $cursor = $collection->find();

        foreach ($cursor as $discount) {
            $conditions = $discount['conditions'];

            foreach ($conditions as $condition) {

                if ($condition['value'] != $product[$condition['target']]) {
                    continue;
                }

                // Sous-niveau
                // En pratique faire du récursif pour parcourir tous les sous niveaux possibles (là j'ai la flemme juste pour tester)
                // (ça pose aussi problème si la 1ere sous condition n'est pas bonne mais la suivante l'est, c'est facile à corriger
                // en retournant true / false en récursif)
                if (true === isset($condition['conditions'])) {
                    foreach ($condition['conditions'] as $subCondition) {

                        if ($subCondition['value'] != $product[$subCondition['target']]) {
                            break 2;
                        }

                        // Since all consecutive conditions are OR we don't need the check the other ones
                        // the code below will be enough in recursive calls
                        break;
                    }
                }

                // If we are here, the product match the conditions, since all consecutive conditions are OR
                // we don't need the check the other ones
                echo 'Discount found: ' . $discount['_id'] . PHP_EOL;
                break;
            }

            // Affiche
            // Discount found: 5a2493c33c95a1281836eb6a
            // Discount found: 5a2493c33c95a1281836eb6b
        }
    }
}