<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class Number extends Model
{

    protected $fillable = [
        'ndate',
        'hundred',
        'ten',
        'unit',
        'first',
        'second',
        'third',
        'fourth',
    ];

    /**
     * @var Crawler[] $nodes
     */
    public static function make(array $nodes, $type): Number
    {
        Log::debug('-----------NUMBER-----------');
        $currentDataIndex = 0;
        $count = count($nodes);
        $entity = new Number();
        for ($i = 0; $i < $count; $i++) {
            $node = $nodes[$i];
            $content = trim($node->text());
            Log::debug($node->html());
            if (empty($content) && $content !== "0") {
                continue;
            }
            $value = $node->first()->text();
            Log::debug($value);
            if ($value === '-') {
                continue;
            }
            Log::debug("$type: $value");

            switch ($currentDataIndex++) {
                case 0:
                    list($month, $day, $year) = explode('/', $value);
                    if((int)$year > 80) {
                        $year = "19$year";
                    }
                    else {
                        $year = "20$year";
                    }
                    $entity->ndate = Carbon::createFromDate("$year", $month, $day);
                    break;
                case 1:
                    $entity->section_id = Section::where('code', Str::upper(trim($value)))->first()->id ?? 2;
                    break;
                case 2:
                    if ($type === 'p3') {
                        $entity->hundred = $value;
                    } else {
                        $entity->first = $value;
                    }
                    break;
                case 3:
                    if ($type === 'p3') {
                        $entity->ten = $value;
                    } else {
                        $entity->second = $value;
                    }
                    break;
                case 4:
                    if ($type === 'p3') {
                        $entity->unit = $value;
                    } else {
                        $entity->third = $value;
                    }
                    break;
                case 5:
                    $entity->fourth = $value;
                    break;
            }
        }
        return $entity;
    }
}
