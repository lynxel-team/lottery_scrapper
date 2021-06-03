<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $configP3 = [
        'type'                  => 'p3',
        'url'                   => 'http://crawler.test/p3_1.htm',
        'columnCount'           => 3,
        'rowCount'              => 53,
        'tupleNodesCount'       => 13,
        'skipHeaderCount'       => 14,
        'skipMidColumnCount'    => 3,
        'skipStartColumnCount'  => 1
    ];
    $configP4 = [
        'type'                  => 'p4',
        'url'                   => 'http://crawler.test/p4_1.htm',
        'columnCount'           => 4,
        'rowCount'              => 50,
        'tupleNodesCount'       => 17,
        'skipHeaderCount'       => 14,
        'skipMidColumnCount'    => 1,
        'skipStartColumnCount'  => 1
    ];
    \App\Jobs\ParseNumberPage::withChain([
        new \App\Jobs\ParseNumberPage($configP4),
    ])->dispatch($configP3);

//    \App\Jobs\ParseNumberPage::dispatch($configP3);
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
