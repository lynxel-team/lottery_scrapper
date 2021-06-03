<?php


namespace App\Actions;


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
            if ($result === 'stop') {
                return 'stop';
            }
        }
    }
}
