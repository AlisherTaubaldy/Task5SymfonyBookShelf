<?php

namespace App\Service;

use Faker\Factory;
use Faker\Generator;

class BookGenerator
{
    private array $locales = [
        'en' => 'en_US',
        'de' => 'de_DE',
        'fr' => 'fr_FR'
    ];

    public function generateBooks(int $page, int $seed, string $language, float $likes, float $reviews): array
    {
        $faker = Factory::create($this->locales[$language] ?? 'en_US');
        $books = [];
        $effectiveSeed = $seed + ($page * 1000);

        mt_srand($effectiveSeed);
        $faker->seed($effectiveSeed);

        for ($i = 1; $i <= 10; $i++) {
            $isbn = $faker->isbn13();
            $books[] = [
                'index' => ($page - 1) * 10 + $i,
                'isbn' => $isbn,
                'title' => ucfirst($faker->realText($maxNbChars = 30, $indexSize = 3)),
                'author' => $faker->name(),
                'publisher' => $faker->company(),
                'likes' => $this->generateFractionalValue($likes),
                'cover' => $this->getPlaceholderBookCover(),
                'reviews' => $this->generateReviews($reviews, $faker)
            ];
        }

        return $books;
    }

    private function generateReviews(float $reviews, Generator $faker): array
    {
        $count = floor($reviews);
        if (rand(0, 100) / 100 < ($reviews - $count)) {
            $count++; // 70% шанс добавить еще 1 рецензию
        }

        $reviewsArray = [];
        for ($i = 0; $i < $count; $i++) {
            $reviewsArray[] = [
                'author' => $faker->name(),
                'text' => $faker->realText(mt_rand(40, 100)),
            ];
        }
        return $reviewsArray;
    }

    private function generateFractionalValue(float $value): int
    {
        $base = floor($value);
        return (rand(0, 100) / 100 < ($value - $base)) ? $base + 1 : $base;
    }

    private function getPlaceholderBookCover(): string
    {
        return "https://picsum.photos/200/300?random=" . rand(1, 1000);
    }
}
