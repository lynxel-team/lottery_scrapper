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
    $config = [
        'type'                  => 'p3',
        'url'                   => 'http://crawler.test/p3.htm',
        'columnCount'           => 4,
        'rowCount'              => 50,
        'tupleNodesCount'       => 13,
        'skipHeaderCount'       => 14,
        'skipMidColumnCount'    => 3,
        'skipStartColumnCount'  => 1
    ];
    \App\Jobs\ParseNumberPage::dispatch($config);
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
