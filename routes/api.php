<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', 'App\Http\Controllers\AuthController@login');

Route::post('/register', 'App\Http\Controllers\AuthController@register');

Route::post('/users/login', 'App\Http\Controllers\UserController@login');
Route::post('/users/register', 'App\Http\Controllers\UserController@register');

Route::group(['middleware' => 'auth:api'], function(){

    Route::group(['prefix' => 'activities'], function(){
        Route::get('/', 'App\Http\Controllers\ActivityController@getGlobalActivities');
        Route::post('/create', 'App\Http\Controllers\ActivityController@createGlobalActivities');
        Route::post('/update', 'App\Http\Controllers\ActivityController@updateGlobalActivities');
        Route::post('/delete', 'App\Http\Controllers\ActivityController@deleteGlobalActivity');
        Route::get('/list', 'App\Http\Controllers\ActivityController@getGlobalActivityList');
    });

    Route::group(['prefix' => 'user-activities'], function(){
        Route::get('/', 'App\Http\Controllers\UserController@getUserActivities');
        Route::post('/create', 'App\Http\Controllers\UserController@createUserActivity');
        Route::post('/update', 'App\Http\Controllers\UserController@updateUserActivities');
        Route::post('/delete', 'App\Http\Controllers\UserController@deleteUserActivities');
    });

    Route::group(['prefix' => 'users'], function(){
        Route::get('/activity-by-date', 'App\Http\Controllers\UserController@getUserActivityByDates');
        Route::get('/username', 'App\Http\Controllers\UserController@getUserNames');
    });

});
