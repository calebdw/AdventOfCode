<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day03 extends AocDay
{
    /** @var Collection<int, list<int>> */
    private Collection $banks;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->banks = $this->lines
            ->filter()
            ->map(fn ($line) => array_map(intval(...), str_split($line)));
    }

    protected function partOne(): ?string
    {
        return (string) $this->banks
            ->reduce(fn ($c, $b) => $c + $this->max($b, 2), 0);
    }

    protected function partTwo(): ?string
    {
        return (string) $this->banks
            ->reduce(fn ($c, $b) => $c + $this->max($b, 12), 0);
    }

    /** @param list<int> $bank */
    private function max(array $bank, int $length): string
    {
        if ($length === 0) {
            return '';
        }

        $count = count($bank);
        $max = 0;
        $index = 0;

        for ($i = 0; $i < ($count - $length + 1); $i++) {
            $num = $bank[$i];

            if ($num > $max) {
                $max = $num;
                $index = $i;
            }
        }

        return $max . $this->max(array_slice($bank, $index + 1), $length - 1);
    }
}
