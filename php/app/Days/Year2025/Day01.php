<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day01 extends AocDay
{
    private const int START = 50;

    private int $dial;

    /** @var Collection<int, Rotation> */
    private Collection $rotations;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->rotations = $this->lines
            ->filter()
            ->map(fn ($line)  => new Rotation(
                direction: Direction::from($line[0]),
                turns: (int) substr($line, 1),
            ));
    }

    protected function partOne(): ?string
    {
        $this->dial = self::START;
        $password = 0;

        foreach ($this->rotations as $rotation) {
            $this->rotate($rotation);

            if ($this->dial === 0) {
                $password++;
            }
        }

        return (string) $password;
    }

    protected function partTwo(): ?string
    {
        $this->dial = self::START;
        $password = 0;

        foreach ($this->rotations as $i => $rotation) {
            $password += $this->rotate($rotation);
        }

        return (string) $password;
    }

    /** @return int the number of times 0 was crossed. */
    private function rotate(Rotation $rotation): int
    {
        if ($rotation->turns === 0) {
            return 0;
        } elseif ($rotation->turns === 100) {
            return 1;
        }

        $original = $this->dial;
        $this->dial += ($rotation->turns % 100) * $rotation->direction->dr();
        $crossed = $this->dial > 99 || ($this->dial <= 0 && $original > 0);
        $this->dial = $this->dial < 0 ? $this->dial + 100 : $this->dial % 100;

        return intdiv($rotation->turns, 100) + (int) $crossed;
    }
}

final readonly class Rotation
{
    public function __construct(
        public Direction $direction,
        public int $turns,
    ) {
    }
}

enum Direction: string
{
    case Left = 'L';
    case Right = 'R';

    public function dr(): int
    {
        return match ($this) {
            self::Left => -1,
            self::Right => 1,
        };
    }
}
