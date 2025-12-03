<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day02 extends AocDay
{
    /** @var Collection<int, array{0: string, 1: string}> */
    private Collection $ranges;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->ranges = $this->lines
            ->filter()
            ->flatMap(fn ($s) => explode(",", $s))
            ->flatten()
            ->map(fn ($s) => explode("-", $s));
    }

    protected function partOne(): ?string
    {
        return (string) $this->ranges
            ->flatMap(function ($range) {
                [$start, $end] = $range;
                $invalid = [];
                $id = $start;

                while ($id <= $end) {
                    $length = strlen($id);

                    if ($length % 2 !== 0) {
                        $id = '1' . str_repeat('0', $length);
                        $length++;
                    }

                    $split = substr($id, 0, intdiv($length, 2));
                    $id = $split . $split;

                    if ($id >= $start && $id <= $end) {
                        $invalid[] = $id;
                    }

                    $split = (string) (((int) $split) + 1);
                    $id = $split . str_repeat('0', intdiv($length, 2));
                }

                return $invalid;
            })
            ->sum();
    }

    protected function partTwo(): ?string
    {
        return (string) $this->ranges
            ->flatMap(function ($range) {
                [$start, $end] = $range;
                $invalid = [];
                $seq = 1;

                $length = strlen($end);
                $maxSeq = $length % 2 === 0
                    ? substr($end, 0, intdiv($length, 2))
                    : str_repeat('9', intdiv($length - 1, 2));

                while ($seq <= $maxSeq) {
                    $id = (string) $seq . (string) $seq;

                    while ($id <= $end) {
                        if ($id >= $start) {
                            $invalid[$id] = true;
                        }

                        $id .= (string) $seq;
                    }

                    $seq++;
                }

                return array_keys($invalid);
            })
            ->sum();
    }
}
