<?php

namespace App\Jobs;

use App\Actions\PageParser;
use App\Models\Number;
use Goutte\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

    protected $url;

    protected $type;
    protected $pageParser;
    protected $pageCount;

    /**
     * @var Crawler
    */
    protected $crawler;

    public function __construct($config)
    {
        $this->url  = $config['url'];
        $this->type  = $config['type'];
        $this->pageCount  = $config['updateCount'];
        $this->pageParser = new PageParser($config);
    }

    public function handle()
    {
        $this->init();
        $tables = $this->crawler->filterXPath('//table');
        $count = $this->pageCount <= 0 ? $tables->count() : min($this->pageCount, $tables->count());
        for ($i = 0; $i < $count; $i++) {
            Log::debug("Traversing: table($i)");
            $node = $tables->eq($i);
            $result = $this->pageParser->withCrawler($node)
                ->parse();
            if ($result === 'stop') {
                return $result;
            }
        }
        return null;
    }

    private function init(): void
    {
        $client = new Client(HttpClient::create(['timeout' => 60]));
        $this->crawler = $client->request('GET', $this->url);
    }
}
