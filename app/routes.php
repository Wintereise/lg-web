<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


Route::get('/', array('as' => 'index', 'uses' => 'requestHandler@showIndex'));
Route::post('process', array('as' => 'process', 'uses' => 'requestHandler@parseProcess'));

Route::filter('requestLimiter', function()
{

    $ip = Request::getClientIp();
    $long = ip2long($ip);
    if(Cache::has($long))
    {
        $reqs = Cache::get($long);

    }
    else
    {
        Cache::put($long, 1, \Carbon\Carbon::now()->addMinutes(60));
        Cache::put($long . "H", date('H'), \Carbon\Carbon::now()->addMinutes(60));
    }
});


