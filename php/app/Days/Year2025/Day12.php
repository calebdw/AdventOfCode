<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day12 extends AocDay
{
    /** @var list<Present> */
    private array $presents;

    /** @var Collection<int, Region> */
    private Collection $regions;

    /** @var array<string, mixed> */
    private static array $__cache = [];

    protected function parseInput(string $input): void
    {
        $sections = collect(explode("\n\n", $input))->filter();
        $regions = $sections->pop();

        $this->presents = collect($sections)
            ->mapWithKeys(function ($section) {
                $lines = collect(explode("\n", $section))->filter();
                [$index, $_] = explode(':', $lines->shift());

                $shape = collect($lines)
                    ->map(
                        fn ($line) => collect(str_split($line))
                            ->map(fn ($char) => $char === '#' ? 1 : 0)
                            ->all(),
                    )
                    ->all();

                return [(int) $index => new Present($shape)];
            })
            ->all();

        $this->regions = collect(explode("\n", $regions))
            ->filter()
            ->map(function ($line) {
                [$size, $shapes] = explode(': ', $line);
                [$width, $length] = explode('x', $size);

                return new Region(
                    (int) $width,
                    (int) $length,
                    array_map(intval(...), explode(' ', $shapes)),
                );
            });
    }

    protected function partOne(): ?string
    {
        return (string) $this->regions
            ->filter($this->canFit(...))
            ->count();
    }

    protected function partTwo(): ?string
    {
        return null;
    }

    private function canFit(Region $region): bool
    {
        $grid = array_fill(0, $region->length, array_fill(0, $region->width, 0));
        $requiredArea = 0;
        $presents = [];

        foreach ($region->quantities as $p => $quantity) {
            $present = $this->presents[$p];
            $requiredArea += $present->area * $quantity;
            for ($q = 0; $q < $quantity; $q++) {
                $presents[] = $present;
            }
        }

        if ($requiredArea > ($region->width * $region->length)) {
            return false;
        }

        return true;

        return $this->backtrack($grid, $presents, $region->length, $region->width);
    }

    /**
     * @param list<list<int>> $grid
     * @param list<Present> $presents
     */
    private function backtrack(array $grid, array $presents, int $length, int $width, int $index = 0): bool
    {
        $present = $presents[$index] ?? null;

        if ($present === null) {
            return true;
        }

        foreach ($this->orientations($present->shape) as $shape) {
            for ($x = 1; $x < $width - 1; $x++) {
                for ($y = 1; $y < $length - 1; $y++) {
                    $newGrid = $this->place($grid, $shape, $x, $y);

                    if ($newGrid === false) {
                        continue;
                    }

                    if ($this->backtrack($newGrid, $presents, $length, $width, $index + 1)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param list<list<int>> $grid
     * @param list<list<int>> $shape
     * @return list<list<int>>
     */
    private function place(array $grid, array $shape, int $x, int $y): array|false
    {
        $rows = count($shape);
        $cols = count($shape[0]);

        for ($i = -1; $i <= 1; $i++) {
            for ($j = -1; $j <= 1; $j++) {
                $grid[$y + $i][$x + $j] += $shape[$i + 1][$j + 1];

                if ($grid[$y + $i][$x + $j] > 1) {
                    return false;
                }
            }
        }

        return $grid;
    }

    /**
     * @param list<list<int>> $shape
     * @return list<list<list<int>>>
     */
    private function orientations(array $shape): array
    {
        $key = serialize($shape);
        $orientations = $this->__cache[$key] ?? [];

        if ($orientations !== []) {
            return $orientations;
        }

        foreach ([$shape, $this->flip($shape)] as $shape) {
            $orientations[serialize($shape)] ??= $shape;
            for ($_ = 0; $_ < 3; $_++) {
                $shape = $this->rotate($shape);
                $orientations[serialize($shape)] ??= $shape;
            }
        }

        return self::$__cache[$key] = array_values($orientations);
    }

    /**
     * @param list<list<int>> $shape
     * @return list<list<int>>
     */
    private function flip(array $shape): array
    {
        return array_reverse($shape);
    }

    /**
     * @param list<list<int>> $shape
     * @return list<list<int>>
     */
    private function rotate(array $shape): array
    {
        $rows = count($shape);
        $cols = count($shape[0]);

        $rotated = [];

        for ($col = 0; $col < $cols; $col++) {
            $newRow = [];
            for ($row = $rows - 1; $row >= 0; $row--) {
                $newRow[] = $shape[$row][$col];
            }
            $rotated[] = $newRow;
        }

        return $rotated;
    }
}

class Present
{
    public int $area;

    public function __construct(
        /** @var list<list<int>> */
        public array $shape,
    ) {
        $this->area = array_sum(array_map(array_sum(...), $shape));
    }
}

class Region
{
    public function __construct(
        public int $width,
        public int $length,
        /** @var list<int> */
        public array $quantities,
     ) {}
}
