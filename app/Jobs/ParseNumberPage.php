<?php

namespace App\Jobs;

use App\Models\Number;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\BrowserKit\HttpBrowser;

class ParseNumberPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $url;

    /**
     * @var Crawler
    */
    protected $cachedTables;

    protected $tableCursor;
    protected $columnCursor;
    protected $rowCursor;
    protected $cursor;
    protected $lastForcedRowIndex;

    protected $skipHeaderCount; //Cantidad de nodos de escape de una tabla
    protected $skipStartColumnCount; //Cantidad de nodos de escape de la primera columna
    protected $skipMidColumnCount; //Cantidad de nodos de escape de la columna en medio
    protected $columnCount; //Cantidad de columnas
    protected $rowCount; //Cantidad de filas
    protected $tupleNodesCount; //Cantidad de datos de una tupla

    /**
     * @var Crawler
    */
    protected $crawler;

    public function __construct($config)
    {
        $this->type                 = $config['type'];
        $this->url                  = $config['url'];
        $this->columnCount          = $config['columnCount'];
        $this->rowCount             = $config['rowCount'];
        $this->tupleNodesCount      = $config['tupleNodesCount'];
        $this->skipHeaderCount      = $config['skipHeaderCount'];
        $this->skipMidColumnCount   = $config['skipMidColumnCount'];
        $this->skipStartColumnCount = $config['skipStartColumnCount'];

        $this->tableCursor = 0;
    }

    public function handle()
    {
        /*parserP3
         *      columnCount: 4,
                rowCount: 50,
                tupleNodesCount: 13,
                skipHeaderCount: 14,
                skipMidColumnCount: 3,
                skipStartColumnCount: 1
         * */
        /*parserP4
         *      columnCount: 4,
                rowCount: 50,
                tupleNodesCount: 17,
                skipHeaderCount: 14,
                skipMidColumnCount: 1,
                skipStartColumnCount: 1
         * */
        $this->init();
        $this->cachedTables = $this->crawler->filterXPath('//table')/*->each(function (\DOMElement $node) {

        })*/;
        $this->moveToStart();

        $item = $this->nextItem();
        dd($item);
    }

    private function init()
    {
        $client = new Client(HttpClient::create(['timeout' => 60]));
        $this->crawler = $client->request('GET', $this->url);
    }

    public function nextItem()
    {
        $changeTable = !$this->canGoNextItem();
        $continueProcess = true;
        $number = null;
        if ($changeTable) {
            if (!$this->canGoNextTable()) {
                $continueProcess = false;
            }
        }

        if ($continueProcess) {
            $data = $this->getCellData();
            if (!empty($data)) {
                $number = Number::make($data, $this->type);
            }
        }
        return $number;
    }

    private function getCellData(): ?array
    {
        $data = [];
        $tableRows = $this->cachedTables->filterXPath("[$this->tableCursor]//tr");
        $currentRowCells = [];
        if ($this->lastForcedRowIndex === 0) {
            $this->lastForcedRowIndex = $this->skipHeaderCount;
        }
        do {
            $index = $this->lastForcedRowIndex + $this->rowCursor;
            $currentRowCells = $tableRows->filterXPath("[$index]//td");
        } while ($this->hasNoRowData($currentRowCells));

        $scrapIndex = $this->getScrapingIndex();
        if ($scrapIndex < $currentRowCells->count()) {
            for ($i = 0; $i < $this->tupleNodesCount; $i++) {
                $index = $scrapIndex + $i;
                $data[$i] = $currentRowCells->filterXPath("[$index]");
            }
            return $data;
        }
        return null;
    }

    private function getScrapingIndex()
    {
        return ($this->columnCursor === 0) ?
            $this->skipStartColumnCount :
            ($this->skipStartColumnCount + $this->columnCursor * $this->tupleNodesCount + $this->columnCursor * $this->skipMidColumnCount);
    }

    private function hasNoRowData(Crawler $rowDataCrawler): bool
    {
        $data = $rowDataCrawler->filterXPath("[1]")->text();
        $valid = empty($data);
        if ($valid) {
            ++$this->lastForcedRowIndex;
        }
        return $valid;
    }

    private function moveToStart()
    {
        $this->rowCursor = 0;
        $this->columnCursor = 0;
        $this->cursor = -1;
        $this->lastForcedRowIndex = 0;
    }

    private function canGoNextTable(): bool
    {
        if ($this->tableCursor + 1 < $this->cachedTables->count()) {
            ++$this->tableCursor;
            $this->moveToStart();
            return true;
        }
        return false;
    }

    private function canGoPrevTable(): bool
    {
        if ($this->tableCursor > 0) {
            --$this->tableCursor;
            $this->moveToStart();
            return true;
        }
        return false;
    }

    private function canGoNextItem(): bool
    {
        $maxSize = $this->rowCount * $this->columnCount;
        if ($this->cursor + 1 < $maxSize) {
            ++$this->cursor;
            $this->updateCursorCoordinates();
            return true;
        }
        return false;
    }

    private function canGoPrevItem(): bool
    {
        if ($this->cursor > 0) {
            --$this->cursor;
            $this->updateCursorCoordinates();
            return true;
        }
        return false;
    }

    private function updateCursorCoordinates(): void
    {
        $this->rowCursor = $this->cursor % $this->rowCount;
        if ($this->rowCursor === 0) {
            $this->lastForcedRowIndex = 0;
        }
        $this->columnCursor = $this->cursor / $this->rowCount;
    }
}