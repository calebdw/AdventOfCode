<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

class Day04 extends AocDay
{
    /** @var list<list<Tile>> */
    private array $map;

    /** @var array<string, true> */
    private array $paper;

    private int $rows;

    private int $cols;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->map = $this->lines
            ->filter()
            ->values()
            ->map(function (string $line, int $y) {
                $tiles = [];
                foreach (str_split($line) as $x => $char) {
                    $tile = Tile::from($char);

                    if ($tile === Tile::Paper) {
                        $this->paper["$x:$y"] = true;
                    }

                    $tiles[] = $tile;
                }

                return $tiles;
            })
            ->all();

        $this->rows = count($this->map);
        $this->cols = count($this->map[0]);
    }

    protected function partOne(): ?string
    {
        return (string) $this->remove(true);
    }

    protected function partTwo(): ?string
    {
        $total = 0;

        do {
            $removed = $this->remove();
            $total += $removed;
        } while ($removed > 0);

        return (string) $total;
    }

    /** @return int total rolls removed */
    private function remove(bool $dryRun = false): int
    {
        $removed = [];

        foreach ($this->paper as $pos => $_) {
            [$x, $y] = explode(':', $pos);
            if ($this->isAccessible((int) $x, (int) $y)) {
                $removed[] = [$x, $y];
            }
        }

        if (! $dryRun) {
            foreach ($removed as [$x, $y]) {
                $this->map[$y][$x] = Tile::Removed;
                unset($this->paper["$x:$y"]);
            }
        }

        return count($removed);
    }

    private function isAccessible(int $x, int $y): bool
    {
        $rolls = 0;

        for ($dy = $y - 1; $dy <= $y + 1; $dy++) {
            for ($dx = $x - 1; $dx <= $x + 1; $dx++) {
                if (
                    $dy < 0 || $dy >= $this->rows
                    || $dx < 0 || $dx >= $this->cols
                    || ($dy === $y && $dx === $x)
                ) {
                    continue;
                }

                if ($this->map[$dy][$dx] === Tile::Paper) {
                    $rolls++;
                }

                if ($rolls > 3) {
                    return false;
                }
            }
        }

        return true;
    }

    private function printMap(): void
    {
        $map = $this->map;

        echo collect($map)
            ->map(fn (array $row) => array_map(fn (Tile $tile) => $tile->value, $row))
            ->map(fn (array $row) => implode('', $row))
            ->implode("\n");
        echo "\n\n";
    }
}

enum Tile: string
{
    case Paper = '@';
    case Empty = '.';
    case Removed = 'x';
}
