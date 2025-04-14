<?php

declare(strict_types=1);

namespace App\Tests\Product\Factory;

use Faker\Factory as FakerFactory;

final class ProductFactory
{
    public static function create(array $overrides = []): array
    {
        $faker = FakerFactory::create();

        return array_merge([
            'name' => $faker->words(3, true),
            'description' => $faker->sentence(),
            'cost' => $faker->numberBetween(10, 1000),
            'tax' => $faker->numberBetween(5, 200),
            'weight' => $faker->numberBetween(1, 50),
            'height' => $faker->numberBetween(10, 100),
            'width' => $faker->numberBetween(10, 100),
            'length' => $faker->numberBetween(10, 100),
        ], $overrides);
    }
}
