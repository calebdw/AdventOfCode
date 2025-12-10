<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

class Day07 extends AocDay
{
    /** @var array{0: int, 1: int} */
    private array $start;

    /** @var list<list<Tile>> */
    private array $map;

    private int $splits;

    private int $timelines;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->map = $this->lines
            ->filter()
            ->values()
            ->map(function (string $line, int $y) {
                $values = array_map(Tile::from(...), str_split($line));

                if (($x = array_search(Tile::Start, $values)) !== false) {
                    $this->start = [$x, $y];
                }

                return $values;
            })
            ->all();
    }

    protected function partOne(): ?string
    {
        if (! isset($this->splits)) {
            $this->trace();
        }

        return (string) $this->splits;
    }

    protected function partTwo(): ?string
    {
        if (! isset($this->timelines)) {
            $this->trace();
        }

        return (string) $this->timelines;
    }

    private function trace(): void
    {
        [$x, $y] = $this->start;
        $map = $this->map;
        $this->splits = 0;
        $this->timelines = 1;
        $beams = [$x => 1];

        do {
            $next = [];
            $y++;

            while (count($beams)) {
                $x = array_key_first($beams);
                $count = $beams[$x];
                unset($beams[$x]);

                $tile = $map[$y][$x] ?? null;

                if ($tile === null) {
                    continue;
                }

                if ($tile === Tile::Splitter) {
                    $this->splits++;
                    $this->timelines += $count;
                    $beams[$x - 1] = ($beams[$x - 1] ?? 0) + $count;
                    $beams[$x + 1] = ($beams[$x + 1] ?? 0) + $count;
                    continue;
                }

                $map[$y][$x] = Tile::Beam;
                $next[$x] = ($next[$x] ?? 0) + $count;
            }


            ksort($next);
            $beams = $next;
        } while ($beams !== []);
    }

    /** @param list<list<Tile>>|null $map */
    private function printMap(?array $map): void
    {
        echo collect($map ?? $this->map)
            ->map(fn (array $row) => implode('', array_map(fn (Tile $tile) => $tile->value, $row)))
            ->implode("\n");
        echo "\n\n";
    }
}

enum Tile: string
{
    case Start = 'S';
    case Empty = '.';
    case Splitter = '^';
    case Beam = '|';
}
