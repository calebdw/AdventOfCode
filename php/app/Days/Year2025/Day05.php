<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day05 extends AocDay
{
    /** @var Collection<int, array{0: int, 1: int}> */
    private Collection $ranges;

    /** @var Collection<int, int> */
    private Collection $ids;

    protected function parseInput(string $input): void
    {
        [$ranges, $ids] = collect(explode("\n\n", $input));

        $this->ranges = collect(explode("\n", $ranges))
            ->filter()
            ->map(fn ($range) => array_map(intval(...), explode('-', $range)))
            ->sortBy(fn ($r) => $r[0])
            ->pipe($this->normalizeRanges(...));

        $this->ids = collect(array_map(intval(...), explode("\n", $ids)))
            ->filter();
    }

    protected function partOne(): ?string
    {
        return (string) $this->ids
            ->filter($this->isFresh(...))
            ->count();
    }

    protected function partTwo(): ?string
    {
        return (string) $this->ranges
            ->map(fn ($r) => $r[1] - $r[0] + 1)
            ->sum();
    }

    /**
     * @param Collection<int, array{0: int, 1: int}> $ranges
     * @return Collection<int, array{0: int, 1: int}>
     */
    private function normalizeRanges(Collection $ranges): Collection
    {
        $normalized = [];

        foreach ($ranges as $range) {
            $isUnique = true;

            foreach ($normalized as $i => $normal) {
                if ($range[1] < $normal[0] || $range[0] > $normal[1]) {
                    continue;
                }

                $normalized[$i] = [
                    min($range[0], $range[1], $normal[0], $normal[1]),
                    max($range[0], $range[1], $normal[0], $normal[1]),
                ];
                $isUnique = false;
                break;
            }

            if ($isUnique) {
                $normalized[] = $range;
            }
        }

        return collect($normalized);
    }


    private function isFresh(int $id): bool
    {
        return $this->ranges->contains(fn ($r) => $r[0] <= $id && $r[1] >= $id);
    }
}
