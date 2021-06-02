<?php


namespace App\Parsers;


class PageParser
{

    protected $node;

    public function __construct($node)
    {
        $this->node = $node;
    }
}
