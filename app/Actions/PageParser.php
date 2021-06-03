<?php


namespace App\Actions;


use Symfony\Component\DomCrawler\Crawler;

class PageParser extends Parser
{
    protected $skipHeaderCount;

    /**
     * @var RowParser $rowParser
     */
    protected $rowParser;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->skipHeaderCount  = $config['skipHeaderCount'];

        $this->rowParser = new RowParser($config);
    }

    public function parse()
    {
        $nodes = $this->crawler->filterXPath("//tr");
        for ($i = 0; $i < $nodes->count(); $i++) {
            $node = $nodes->eq($i);
            $result = $this->rowParser->withCrawler($node)
                ->parse();
            if ($result === 'stop') {
                return 'stop';
            }
        }
    }
}
