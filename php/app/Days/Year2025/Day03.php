<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

class Day03 extends AocDay
{
    protected function partOne(): ?string
    {
        return $this->total(2);
    }

    protected function partTwo(): ?string
    {
        return $this->total(12);
    }

    private function total(int $places): string
    {
        return (string) $this->lines->reduce(fn ($c, $b) => $c + $this->max($b, $places));
    }

    private function max(string $bank, int $length, int $index = 0): string
    {
        if ($length === 0) {
            return '';
        }

        $count = strlen($bank);
        $max = 0;

        for ($i = $index; $i <= ($count - $length); $i++) {
            $num = $bank[$i];

            if ($num > $max) {
                $max = $num;
                $index = $i;
            }
        }

        return $max . $this->max($bank, $length - 1, $index + 1);
    }
}
