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
//        'url'                   => 'http://crawler.test/p3_1.htm',
        'url'                   => 'https://www.flalottery.com/exptkt/p3.htm',
        'updateCount'           => 3,           //Cantidad de tablas a actualizar [0]-> Todas
    ];
    $configP4 = [
        'type'                  => 'p4',
//        'url'                   => 'http://crawler.test/p4_1.htm',
        'url'                   => 'https://www.flalottery.com/exptkt/p4.htm',
        'updateCount'           => 4,           //Cantidad de tablas a actualizar [0]-> Todas
    ];
    \App\Jobs\ParseNumberPage::withChain([
        new \App\Jobs\ParseNumberPage($configP4),
    ])->dispatch($configP3);

//    \App\Jobs\ParseNumberPage::dispatch($configP3);
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
