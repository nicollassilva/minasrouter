<?php
    require __DIR__ . "/../vendor/autoload.php";
    
use MinasRouter\Router\Route;

// The second argument is optional. It separates the Controller and Method from the string
// Example: "Controller@method"
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
        
        // the same as:
        //Route::get('/users/{id}', 'App\Controllers\UserController@show')->name('users.show')->whereNumber('id');
    });


// ... all routes here

// You will put all your routes before this function
Route::execute();