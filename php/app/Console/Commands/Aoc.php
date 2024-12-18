<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Concerns\ProfilesClosures;
use App\Days\AocDay;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use RuntimeException;

/** @phpstan-import-type ProfiledResult from ProfilesClosures */
class Aoc extends Command
{
    use ProfilesClosures;

    protected $signature = 'aoc
        {input? : The challenge input}
        {--f|file= : The file to read the input from}
        {--y|year= : The year to execute, if null then executes current year}
        {--d|day= : The day to execute, if null then executes all days}
        {--p|part= : The part to execute, if null then executes all parts}
        {--s|submit : Submit the solution to AoC}
    ';

    protected $description = 'Execute AoC solutions.';

    public function __invoke(): void
    {
        $year = $this->option('year') ?? date('Y');
        $input = $this->getProgramInput();

        $this->newLine();
        $this->components->info("Advent of Code {$year}");

        $this->registerDays($year, $this->option('day'))
            ->tap(function ($days) {
                if ($days->count() > 1 && $this->option('submit')) {
                    throw new RuntimeException('Cannot submit multiple days at once.');
                }
            })
            ->whenEmpty(fn () => $this->components->warn('No days found.'))
            ->each(function ($day) use ($input, $year) {
                $input ??= $this->getDayInput($day, $year);

                $result = $this->profile(fn () => $day($input, (int) $this->option('part')));

                $this->components->twoColumnDetail(
                    "<fg=green;options=bold>{$day->label()}</>",
                    $this->formatProfile($result),
                );

                if ($this->option('submit')) {
                    if (($result['value'][1]['value'] ?? null) === null) {
                        $this->submit($year, $day, 1, $result['value'][0]['value']);
                    } else {
                        $this->submit($year, $day, 2, $result['value'][1]['value']);
                    }
                }

                $this->components->twoColumnDetail('Part one', $this->formatResult($result['value'][0]));
                $this->components->twoColumnDetail('Part two', $this->formatResult($result['value'][1]));
                $this->newLine();
            });
    }

    /** @param ?ProfiledResult $result */
    private function formatResult(?array $result): string
    {
        if ($result === null) {
            return "<fg=blue;options=bold>SKIPPED</>";
        }

        if ($result['value'] === null) {
            return "<fg=yellow;options=bold>INCOMPLETE</>";
        }

        return "{$this->formatProfile($result)} {$result['value']}";
    }

    /** @param ProfiledResult $result */
    private function formatProfile(array $result): string
    {
        $time = number_format($result['time_ms'], 3);
        $memory = number_format($result['memory_kb'], 3);

        return "<fg=gray>{$time} ms, {$memory} kB</>";
    }

    private function getProgramInput(): ?string
    {
        if (! posix_isatty(STDIN)) {
            return file_get_contents('php://stdin');
        }

        if ($file = $this->option('file')) {
            return file_get_contents($file);
        }

        return $this->argument('input');
    }

    private function getDayInput(AocDay $day, string $year): string
    {
        $file = Str::camel(class_basename($day)) . '.txt';
        $path = dirname(__DIR__, levels: 4) . "/inputs/{$year}/{$file}";

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        $session = env('AOC_SESSION');
        throw_unless($session, 'No session cookie found. Please set the AOC_SESSION environment variable.');

        $response = Http::withHeaders(['Cookie' => "session={$session}"])
            ->get("https://adventofcode.com/{$year}/day/{$day->day()}/input")
            ->throw();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, recursive: true);
        }

        file_put_contents($path, $response->body());

        return $response->body();
    }

    private function submit(string $year, AocDay $day, int $level, string $answer): void
    {
        $session = env('AOC_SESSION');
        $body = Http::asForm()
            ->withHeaders([
                'Cookie' => "session={$session}",
            ])
            ->post("https://adventofcode.com/{$year}/day/{$day->day()}/answer", [
                'level' => (string) $level,
                'answer' => $answer,
            ])
            ->throw()
            ->body();

        if (Str::contains($body, 'That\'s the right answer')) {
            $this->components->info('Answer submitted successfully.');
        } else {
            $this->components->error('Answer submission failed.');
            echo $body;
        }
    }

    /** @return Collection<int, AocDay> */
    private function registerDays(string $year, ?string $day = null): Collection
    {
        $path = "Days/Year{$year}";

        if (! is_null($day)) {
            $path .= "/Day" . str_pad($day, 2, '0', STR_PAD_LEFT);
        }

        return collect()
            ->wrap(iterator_to_array(
                Finder::create()->files()
                    ->in([app_path()])
                    ->path($path),
            ))
            ->map(fn ($file) => str_replace(
                search: ['app/', '/', '.php'],
                replace: ['App\\', '\\', ''],
                subject: Str::after($file->getRealPath(), base_path() . DIRECTORY_SEPARATOR),
            ))
            ->filter(fn ($class) => is_subclass_of($class, AocDay::class))
            ->sort()
            ->values()
            ->map(fn ($class) => new $class((bool) $this->option('verbose')));
    }
}
