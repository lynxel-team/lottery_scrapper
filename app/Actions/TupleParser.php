<?php


namespace App\Actions;


use App\Models\Number;
use App\Models\Section;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Symfony\Component\Translation\t;

class TupleParser extends Parser
{
    protected $startPosition;

    protected $endPosition;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function withStartPosition($position)
    {
        $this->startPosition = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndPosition()
    {
        return $this->endPosition;
    }

    public function parse()
    {
        Log::debug('-------------TUPLA---------------');
        $cursor = $this->startPosition;
        $tds = $this->crawler->filterXPath('//td');

        $date       = null;
        $section    = null;
        $hundred    = null;
        $ten        = null;
        $unit       = null;
        $first      = null;
        $second     = null;
        $third      = null;
        $fourth     = null;

        while ($cursor >= 0 && $cursor < $tds->count()) {
            $element = $tds->eq($cursor++);
            $value = trim($element->text());
            Log::debug($element->html());
            if (empty($value) && $value !== '0') {
                continue;
            }

            if ($date === null) {
                $date = $this->parseDate($value);
                Log::debug("Fecha: $date");
                continue;
            }
            if ($section === null) {
                $section = $this->parseSection($value);
                Log::debug("Seccion: $section->id");
                continue;
            }

            if ($this->type === 'p3') {
                if ($hundred === null) {
                    $hundred = $this->parseNumber($value);
                    Log::debug("Hundred: $hundred");
                    continue;
                }
                if ($ten === null) {
                    $ten = $this->parseNumber($value);
                    Log::debug("Ten: $ten");
                    continue;
                }
                if ($unit === null) {
                    $unit = $this->parseNumber($value);
                    Log::debug("Unit: $unit");
                    if ($unit !== null) {
                        $this->resolveEndPosition($cursor, $tds);
                        return $this->makeP3Item($date, $section, $hundred, $ten, $unit);
                    }
                }
            }
            else {
                if ($first === null) {
                    $first = $this->parseNumber($value);
                    Log::debug("First: $first");
                    continue;
                }
                if ($second === null) {
                    $second = $this->parseNumber($value);
                    Log::debug("Second: $second");
                    continue;
                }
                if ($third === null) {
                    $third = $this->parseNumber($value);
                    Log::debug("Third: $third");
                    continue;
                }
                if ($fourth === null) {
                    $fourth = $this->parseNumber($value);
                    Log::debug("Four: $fourth");
                    if ($fourth !== null) {
                        $this->resolveEndPosition($cursor, $tds);
                        return $this->makeP4Item($date, $section, $first, $second, $third, $fourth);
                    }
                }
            }
        }

        return null;
    }

    private function makeP3Item($date, $section, $hundred, $ten, $unit)
    {
        $number = Number::where('ndate', $date->format('Y-m-d'))
            ->where('section_id', $section->id)
            ->first();
        if ($number) {
            if ($this->entityIsNotFilled($number)) {
                $number->fill([
                    'hundred' => $hundred,
                    'ten' => $ten,
                    'unit' => $unit,
                ]);
                $number->save();
                return $number;
            }
            return 'stop';
        }

        return Number::create([
            'ndate' => $date,
            'hundred' => $hundred,
            'ten' => $ten,
            'unit' => $unit,
            'section_id' => $section->id,
        ]);
    }

    private function makeP4Item($date, $section, $first, $second, $third, $fourth)
    {
        $number = Number::where('ndate', $date->format('Y-m-d'))
            ->where('section_id', $section->id)
            ->first();
        if ($this->entityIsNotFilled($number)) {
            if ($number) {
                $number->fill([
                    'first' => $first,
                    'second' => $second,
                    'third' => $third,
                    'fourth' => $fourth,
                ]);
                $number->save();
            } else {
                $number = Number::create([
                    'ndate' => $date,
                    'first' => $first,
                    'second' => $second,
                    'third' => $third,
                    'fourth' => $fourth,
                    'section_id' => $section->id,
                ]);
            }

            return $number;
        }

        return 'stop';
    }

    private function entityIsNotFilled(Number $number)
    {
        return $number->ndate === null
            || $number->hundred === null
            || $number->ten === null
            || $number->unit === null
            || $number->first === null
            || $number->second === null
            || $number->third === null
            || $number->fourth === null
            || $number->section_id === null;
    }

    private function resolveEndPosition($cursor, $tds): void
    {
        Log::debug("Find end position +$cursor");
        $this->endPosition = -1;
        while($this->endPosition === -1 && $cursor < $tds->count()) {
            $td = $tds->eq($cursor);
            $data = trim($td->text());
            if (!empty($data)) {
                $matches = explode('/', $data);
                if (count($matches) === 3) {
                    $this->endPosition = $cursor;
                }
            }
            ++$cursor;
        }
    }

    public function parseDate($value): ?Carbon
    {
        [$month, $day, $year] = explode('/', $value);
        if (!empty($year)) {
            if ((int)$year > 80) {
                $year = "19$year";
            } else {
                $year = "20$year";
            }
            return Carbon::createFromDate($year, $month, $day);
        }
        return null;
    }

    public function parseNumber($value): ?int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int)$value;
        }
        return null;
    }

    public function parseSection($value): ?Section
    {
        $data = Str::contains($value, 'E') ? 'E' : 'M';
        return Section::where('code', $data)->first();
    }
}
