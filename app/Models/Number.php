<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        $currentDataIndex = 0;
        $count = count($nodes);
        $entity = new Number();
        for ($i = 0; $i < $count; $i++) {
            $node = $nodes[$i];
            $content = $node->text();
            if (empty($content)) {
                continue;
            }
            $value = $node->first()->text();
            if ($value === '-') {
                continue;
            }

            switch ($currentDataIndex++) {
                case 0:
                    list($day, $month, $year) = explode('/', $value);
                    $entity->ndate = Carbon::createFromDate($year, $month, $day);
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
