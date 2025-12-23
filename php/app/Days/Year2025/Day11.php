<?php

declare(strict_types=1);

namespace App\Days\Year2025;

use App\Days\AocDay;

class Day11 extends AocDay
{
    /** @var array<string, list<string>> */
    private array $devices;

    /** @var array<string, int> */
    private static array $__cache = [];

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->devices = $this->lines
            ->filter()
            ->mapWithKeys(function ($line) {
                [$label, $devices] = explode(': ', $line);

                return [$label => explode(' ', $devices)];
            })
            ->all();
    }

    protected function partOne(): ?string
    {
        return (string) $this->walk($this->devices['you']);
    }

    protected function partTwo(): ?string
    {
        self::$__cache = [];

        return (string) $this->walk($this->devices['svr'], ['dac', 'fft']);
    }

    /**
     * @param list<string> $devices
     * @param list<string> $reqs
     * @param array<string, bool> $visited
     */
    private function walk(array $devices, array $reqs = [], array $visited = []): int
    {
        $paths = 0;

        foreach ($devices as $device) {
            if ($device === 'out') {
                if ($reqs === [] || array_all($reqs, fn ($r) => isset($visited[$r]))) {
                    return 1;
                }

                return 0;
            }

            if (isset($visited[$device])) {
                continue;
            }

            $bitmask = 0;
            foreach ($reqs as $i => $req) {
                if (isset($visited[$req])) {
                    $bitmask |= (1 << $i);
                }
            }

            $key = "$device:$bitmask";
            $cached = self::$__cache[$key] ?? null;

            if ($cached !== null) {
                $paths += $cached;
                continue;
            }

            $result = $this->walk($this->devices[$device], $reqs, [...$visited, $device => true]);
            self::$__cache[$key] = $result;
            $paths += $result;
        }

        return $paths;
    }
}
