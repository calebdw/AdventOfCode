<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

/**
 * @phpstan-type Point array{0: int, 1: int}
 * @phpstan-type Line array{0: Point, 1: Point}
 */
class Day09 extends AocDay
{
    /** @var array<string, Point> */
    private array $points;

    /** @var array<int, int> */
    private array $xMap;

    /** @var array<int, int> */
    private array $yMap;

    /** @var array<string, bool> */
    private array $contained = [];

    /** @var list<array{0: int, 1: int, 2: int}> */
    private array $horizontal = [];

    /** @var array<string, int> */
    private array $areas = [];

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->points = $this->lines
            ->filter()
            ->values()
            ->mapWithKeys(fn ($l) => [$l => array_map(intval(...), explode(',', $l))])
            ->tap(function ($p) {
                $this->areas = $this->computeAreas($p->all());
            })
            ->pipe(fn ($p) => $this->compress($p->all()));
    }

    protected function partOne(): ?string
    {
        return (string) array_values($this->areas)[0];
    }

    protected function partTwo(): ?string
    {
        foreach ($this->areas as $key => $area) {
            [$i, $j] = explode(':', $key);
            [$x1, $y1] = explode(',', $i);
            [$x2, $y2] = explode(',', $j);

            $x1n = $this->xMap[$x1];
            $y1n = $this->yMap[$y1];
            $x2n = $this->xMap[$x2];
            $y2n = $this->yMap[$y2];

            if ($this->isFullyContained([$x1n, $y1n], [$x2n, $y2n])) {
                return (string) $area;
            }
        }

        return 'error';
    }

    /**
     * @param list<Point> $points
     * @return array<string, int>
     */
    private function computeAreas(array $points): array
    {
        $areas = [];
        foreach ($points as $i => $a) {
            foreach ($points as $j => $b) {
                if ($i >= $j || $a[0] === $b[0] || $a[1] === $b[1]) {
                    continue;
                }
                $areas["{$i}:{$j}"] ??= $this->area($a, $b);
            }
        }
        arsort($areas);
        return $areas;
    }

    /**
     * @param Point $a
     * @param Point $b
     */
    private function isFullyContained(array $a, array $b): bool
    {
        $xmin = min($a[0], $b[0]);
        $ymin = min($a[1], $b[1]);
        $ymax = max($a[1], $b[1]);

        // check opposite corners first (fail fast)
        if (! $this->isContained($a[0], $b[1]) || ! $this->isContained($b[0], $a[1])) {
            return false;
        }

        // scan left edge
        for ($y = $ymin + 1; $y < $ymax; $y++) {
            if (! $this->isContained($xmin, $y)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given point is contained in the grid by ray casting vertically.
     */
    private function isContained(int $x, int $y): bool
    {
        $key = "{$x},{$y}";
        $contained = $this->contained[$key] ?? null;

        if ($contained !== null) {
            return $contained;
        }

        if ($this->horizontal === []) {
            $points = array_values($this->points);
            $count = count($points);

            foreach ($points as $i => $a) {
                $b = $points[($i + 1) % $count];

                if ($a[1] === $b[1]) {
                    $this->horizontal[] = [$a[1], min($a[0], $b[0]), max($a[0], $b[0])];
                }
            }
            usort($this->horizontal, fn ($a, $b) => $b[0] <=> $a[0]);
        }

        $crosses = 0;

        foreach ($this->horizontal as [$ymin, $xmin, $xmax]) {
            if ($y > $ymin) {
                break;
            }

            // only count crossings at the left edge
            if ($x >= $xmin && $x < $xmax) {
                if ($y === $ymin) {
                    return $this->contained[$key] = true;
                }
                $crosses++;
            }
        }

        return $this->contained[$key] = $crosses % 2 === 1;
    }

    /**
     * Find the area for the given opposing points.
     *
     * @param Point $a
     * @param Point $b
     */
    private function area(array $a, array $b): int
    {
        return (abs($a[0] - $b[0]) + 1) * (abs($a[1] - $b[1]) + 1);
    }

    /**
     * @param list<Point> $points
     * @return array<string, Point>
     */
    private function compress(array $points): array
    {
        $result = [];

        $xMap = array_unique(array_column($points, 0));
        $yMap = array_unique(array_column($points, 1));
        sort($xMap);
        sort($yMap);
        $this->xMap = array_flip($xMap);
        $this->yMap = array_flip($yMap);

        foreach ($points as [$x, $y]) {
            $xn = $this->xMap[$x];
            $yn = $this->yMap[$y];
            $result["{$xn},{$yn}"] = [$xn, $yn];
        }

        return $result;
    }
}
