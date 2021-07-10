<?php


namespace App\Actions;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class PageParser extends Parser
{
    /**
     * @var RowParser $rowParser
     */
    protected $rowParser;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->rowParser = new RowParser($config);
    }

    public function parse()
    {
        $nodes = $this->crawler->filterXPath("//tr");
        for ($i = 0; $i < $nodes->count(); $i++) {
            Log::debug("Traversing: row($i)");
            $node = $nodes->eq($i);
            $result = $this->rowParser->withCrawler($node)
                ->parse();
            if ($this->checkStopCondition($result)) {
                return 'stop';
            }
        }

        return null;
    }

    private function checkStopCondition($result)
    {
        if (!empty($result)) {
            $firstNumber = $result[0];
            $lastPivotDate = Cache::get("last_number_date");
            return ($firstNumber && $firstNumber->ndate && !empty($lastPivotDate) && $firstNumber->ndate->isBefore(Carbon::createFromFormat('Y-m-d H:i:s', $lastPivotDate)));
        }
        return false;
    }
}
