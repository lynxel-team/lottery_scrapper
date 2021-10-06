<?php

namespace App\Console\Commands;

use App\Jobs\ParseNumberPage;
use App\Models\Number;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateNumbersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for update numbers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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
//            'url'                   => 'http://crawler.test/p3.htm',
            'url'                   => 'https://www.flalottery.com/exptkt/p3.htm',
            'updateCount'           => env('MAX_P3_TABLES', 1),           //Cantidad de tablas a actualizar [0]-> Todas
        ];
        $configP4 = [
            'type'                  => 'p4',
//            'url'                   => 'http://crawler.test/p4.htm',
            'url'                   => 'https://www.flalottery.com/exptkt/p4.htm',
            'updateCount'           => env('MAX_P4_TABLES', 1),           //Cantidad de tablas a actualizar [0]-> Todas
        ];

        ParseNumberPage::withChain([
            new ParseNumberPage($configP4),
        ])->dispatch($configP3);
    }
}
