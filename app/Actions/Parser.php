<?php


namespace App\Actions;


use Symfony\Component\DomCrawler\Crawler;

abstract class Parser
{
    protected $type;

    /**
     * @var Crawler $crawler
     */
    protected $crawler;

    public function __construct($config)
    {
        $this->type = $config['type'];
    }

    abstract public function parse();

    public function withCrawler($crawler)
    {
        $this->crawler = $crawler;
        return $this;
    }
}
