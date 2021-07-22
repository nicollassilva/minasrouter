<?php
    require __DIR__ . "/../vendor/autoload.php";
    
use MinasRouter\Router\Route;

Route::start("http://localhost", "@");

Route::get("/", function() {
    echo 'Welcome to MinasRouter!';
});

Route::get('/about', 'App\Controllers\WebController@about')->name('web.about');

Route::namespace('App\Controllers')
    ->prefix('users')
    ->name('users')
    ->group(function() {
        Route::get('/{id}', 'UserController@show')->name('show')->whereNumber('id');
    });

Route::execute();
