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

Route::pattern('id', '[0-9]+');

Route::get('/', 'HomeController@getIndex');

Route::resource('user', 'UserController');

Route::get('login', 'AuthController@getLogin')->before('guest');
Route::get('logout', 'AuthController@getLogout')->before('auth');
Route::controller('auth', 'AuthController');

Route::controller('config', 'ConfigController');