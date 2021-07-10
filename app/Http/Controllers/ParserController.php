<?php


namespace App\Http\Controllers;


use App\Models\Number;
use Illuminate\Support\Facades\Cache;

class ParserController
{
    public function index()
    {
        $date = null;
        $pivot = Number::where('section_id', 2)
            ->orderBy('ndate', 'desc')
            ->first();
        if ($pivot) {
            $date = $pivot->ndate->subDay();
        }
        Cache::forever("last_number_date", $date);

        $configP3 = [
            'type'                  => 'p3',
            'url'                   => 'http://crawler.test/p3.htm',
//        'url'                   => 'https://www.flalottery.com/exptkt/p3.htm',
            'updateCount'           => 3,           //Cantidad de tablas a actualizar [0]-> Todas
        ];
        $configP4 = [
            'type'                  => 'p4',
            'url'                   => 'http://crawler.test/p4.htm',
//        'url'                   => 'https://www.flalottery.com/exptkt/p4.htm',
            'updateCount'           => 4,           //Cantidad de tablas a actualizar [0]-> Todas
        ];
        \App\Jobs\ParseNumberPage::withChain([
            new \App\Jobs\ParseNumberPage($configP4),
        ])->dispatch($configP3);
        return view('welcome');
    }
}
