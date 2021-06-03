<?php


namespace App\Actions;


use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class RowParser extends Parser
{
    /**
     * @var TupleParser $tupleParser
    */
    protected $tupleParser;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->tupleParser = new TupleParser($config);
    }

    public function parse()
    {
        $items = [];
        $tokenPosition = 0;
        do {
            $tokenPosition = $this->findNextDateToken($tokenPosition);
            $item = $this->tupleParser->withCrawler($this->crawler)
                ->withStartPosition($tokenPosition)
                ->parse();
            if ($item && $item !== 'stop') {
                $items[] = $item;
                $tokenPosition = $this->tupleParser->getEndPosition();
            }
            else if ($item === 'stop') {
                return 'stop';
            }
        } while ($tokenPosition >= 0);

        return $items;
    }

    private function findNextDateToken($fromPosition = 0): int
    {
        $cursor = $fromPosition;
        $index = -1;
        $tds = $this->crawler->filterXPath('//td');
        $count = $tds->count();
        while ($index === -1 && $cursor < $count) {
            $td = $tds->eq($cursor);
            Log::debug("Find date ($cursor)");

            $data = trim($td->text());
            Log::debug("Row DT: $data");
            if (!empty($data)) {
                $matches = explode('/', $data);
                if ($index === -1 && count($matches) === 3) {
                    Log::debug("Encontrada DT: $cursor");
                    return $cursor;
                }
            }

            ++$cursor;
        }
        return $index;
    }
}
