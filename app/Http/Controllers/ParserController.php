<?php


namespace App\Http\Controllers;


use App\Jobs\ParseNumberPage;
use App\Models\Number;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ParserController
{
    public function index()
    {
        Artisan::call('numbers:update');
        return view('welcome');
    }
}
