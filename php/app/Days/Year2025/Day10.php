<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day10 extends AocDay
{
    /** @var Collection<int, Machine> */
    private Collection $machines;

    /** @var array<string, mixed> */
    private static array $__cache = [];

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->machines = $this->lines
            ->filter()
            ->values()
            ->map(fn ($l) => new Machine($l));
    }

    protected function partOne(): ?string
    {
        return (string) $this->machines
            ->map(fn ($m) => min($this->buildParityLookup($m->buttons)[$m->diagram] ?? []))
            ->sum();
    }

    protected function partTwo(): ?string
    {
        return (string) $this->machines
            ->map(fn ($m) => $this->minPressesForJoltage($m->buttons, $m->joltages))
            ->sum();
    }

    /**
     * @param list<int> $buttons
     * @param list<int> $joltages
     */
    private function minPressesForJoltage(array $buttons, array $joltages): int
    {
        $key = serialize($buttons) . serialize($joltages);
        $minPresses = self::$__cache[$key] ?? null;

        if ($minPresses !== null) {
            return $minPresses;
        }

        if (array_all($joltages, fn ($v) => $v === 0)) {
            return self::$__cache[$key] = 0;
        }

        $parityLookup = $this->buildParityLookup($buttons);
        $configurations = $parityLookup[$this->parity($joltages)] ?? [];

        if ($configurations === []) {
            return self::$__cache[$key] = PHP_INT_MAX;
        }

        $numberOfButtons = count($buttons);
        $numberOfJoltages = count($joltages);

        return self::$__cache[$key] = collect($configurations)
            ->map(function ($presses, $configuration) use ($buttons, $joltages, $numberOfButtons, $numberOfJoltages) {
                for ($b = 0; $b < $numberOfButtons; $b++) {
                    if ($configuration & (1 << ($numberOfButtons - $b - 1))) {
                        $button = $buttons[$b];

                        for ($j = 0; $j < $numberOfJoltages; $j++) {
                            if ($button & (1 << ($numberOfJoltages - $j - 1))) {
                                $joltages[$j]--;
                            }
                        }
                    }
                }

                if (array_any($joltages, fn ($v) => $v < 0)) {
                    return PHP_INT_MAX;
                }

                $joltages = array_map(fn ($v) => $v >> 1, $joltages);
                $result = $this->minPressesForJoltage($buttons, $joltages);

                if ($result === PHP_INT_MAX) {
                    return PHP_INT_MAX;
                }

                return 2 * $result + $presses;
            })
            ->min();
    }

    /** @param list<int> $numbers */
    private function parity(array $numbers): int
    {
        $parity = 0;
        $shift = count($numbers) - 1;

        foreach ($numbers as $n) {
            $parity |= ($n & 1) << $shift;
            $shift--;
        }

        return $parity;
    }

    /**
     * @param list<int> $buttons
     * @return array<int, array<int, int>>
     */
    private function buildParityLookup(array $buttons): array
    {
        $key = serialize($buttons);
        $lookup = self::$__cache[$key] ?? [];

        if ($lookup !== []) {
            return $lookup;
        }

        $numberOfButtons = count($buttons);
        $max = 2 ** $numberOfButtons;

        for ($configuration = 0; $configuration < $max; $configuration++) {
            $value = 0;
            $presses = 0;

            for ($b = 0; $b < $numberOfButtons; $b++) {
                if ($configuration & (1 << ($numberOfButtons - $b - 1))) {
                    $value ^= $buttons[$b];
                    $presses++;
                }
            }

            $lookup[$value][$configuration] = $presses;
        }

        return self::$__cache[$key] = $lookup;
    }
}

class Machine
{
    public int $diagram;

    /** @var list<int> */
    public array $buttons;

    /** @var list<int> */
    public array $joltages;

    public function __construct(string $input)
    {
        $this->diagram = Str::of($input)
            ->match('/\[.*\]/')
            ->unwrap('[', ']')
            ->replace(['.', '#'], ['0', '1'])
            ->toInteger(2);

        $this->joltages = Str::of($input)
            ->match('/{.*}/')
            ->unwrap('{', '}')
            ->explode(',')
            ->map(fn ($c) => (int) $c)
            ->all();

        $count = count($this->joltages);

        $this->buttons = Str::of($input)
            ->matchAll('/\(([^)]*)\)/')
            ->map(
                fn ($b) => collect(explode(',', $b))
                    ->map(fn ($c) => (int) $c)
                    ->reduce(fn ($c, $v) => $c | 1 << ($count - $v - 1), 0)
            )
            ->all();
    }
}
