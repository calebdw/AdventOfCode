<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

class Day08 extends AocDay
{
    /** @var array<string, array{0: int, 1: int, 2: int}> */
    private array $points;

    /** @var list<array<string, true>> */
    private array $circuits;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->points = $this->lines
            ->filter()
            ->values()
            ->mapWithKeys(fn ($l) => [$l => array_map(intval(...), explode(',', $l))])
            ->all();
    }

    protected function partOne(): ?string
    {
        return (string) $this->connect(count($this->points));
    }

    protected function partTwo(): ?string
    {
        return (string) $this->connect();
    }

    private function connect(?int $limit = null): int
    {
        $distances = [];

        foreach ($this->points as $i => $a) {
            foreach ($this->points as $j => $b) {
                if ($i >= $j) {
                    continue;
                }
                $distances["{$i}:{$j}"] ??= $this->distance($a, $b);
            }
        }
        asort($distances);

        if ($limit !== null) {
            $distances = array_slice($distances, 0, $limit);
        }

        $circuits = array_map(fn ($d) => [$d => true], array_keys($this->points));

        foreach ($distances as $key => $distance) {
            [$i, $j] = explode(':', $key);

            $ikey = array_find_key($circuits, fn ($c) => isset($c[$i]));
            assert($ikey !== null);
            $jkey = array_find_key($circuits, fn ($c) => isset($c[$j]));
            assert($jkey !== null);

            if ($ikey === $jkey) {
                continue;
            }

            $circuits[$ikey] = [...$circuits[$ikey], ...$circuits[$jkey]];
            unset($circuits[$jkey]);

            if (count($circuits) === 1) {
                return $this->points[$i][0] * $this->points[$j][0];
            }
        }

        $counts = array_map(fn ($c) => count($c), $circuits);
        rsort($counts);

        return array_product(array_slice($counts, 0, 3));

    }

    /**
     * @param array{0: int, 1: int, 2: int} $a
     * @param array{0: int, 1: int, 2: int} $b
     */
    private function distance(array $a, array $b): float
    {
        return sqrt(($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2 + ($a[2] - $b[2]) ** 2);
    }
}
