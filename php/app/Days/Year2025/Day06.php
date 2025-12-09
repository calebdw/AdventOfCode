<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use RuntimeException;

class Day06 extends AocDay
{
    private string $signs;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $lines = $this->lines->filter();

        $this->signs = $lines->pop();
        $this->lines = $lines;


    }

    protected function partOne(): ?string
    {
        $signs = collect(explode(' ', $this->signs))
            ->filter()
            ->values()
            ->all();

        return (string) $this->lines
            ->map(
                fn ($l) => collect(explode(' ', $l))
                    ->filter()
                    ->map(fn ($n) => (int) $n)
                    ->values()
                    ->all(),
            )
            ->values()
            ->pipe(fn ($p) => collect($this->transpose($p->all())))
            ->map(fn ($p, $i) => $this->calculate($p, $signs[$i]))
            ->sum();
    }

    protected function partTwo(): ?string
    {
        $values = [];
        $column = 0;
        $result = 0;
        $sign = '';

        for ($i = strlen($this->signs) - 1; $i >= -1; $i--) {
            if ($sign === '') {
                foreach ($this->lines as $line) {
                    $values[$column] ??= '';
                    $values[$column] .= trim($line[$i]);
                }
            } else {
                $result += $this->calculate(array_map(intval(...), $values), $sign);
                $values = [];
                $column = 0;
            }

            $sign = trim($this->signs[$i]);
            $column++;
        }

        return (string) $result;
    }

    /**
     * @param list<list<mixed>> $array
     * @return list<list<mixed>>
     */
    private function transpose(array $array): array
    {
        return array_map(null, ...$array);

    }

    /** @param list<int> $values */
    private function calculate(array $values, string $sign): int
    {
        return match ($sign) {
            '*' => array_product($values),
            '+' => array_sum($values),
            default => throw new RuntimeException("Invalid sign: {$sign}"),
        };
    }
}
