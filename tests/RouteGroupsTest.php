<?php

use MinasRouter\Router\Route;
use PHPUnit\Framework\TestCase;
use MinasRouter\Router\RouteGroups;

final class RouteGroupsTest extends TestCase
{
    /**
     * @test
     */
    public function check_if_static_methods_of_route_change_in_groups_return_an_instance_of_RouteGroups()
    {
        Route::start("http://localhost");

        $this->assertInstanceOf(RouteGroups::class, Route::namespace("App\Controllers"));

        $this->assertInstanceOf(RouteGroups::class, Route::name("admin."));

        $this->assertInstanceOf(RouteGroups::class, Route::prefix("admin/"));

        $this->assertInstanceOf(RouteGroups::class, Route::middleware("test, test2"));
    }

    /**
     * @test
     */
    public function check_if_the_names_of_all_routes_within_the_group_change()
    {
        Route::name("admin.")->group(function () {
            $index = Route::get("/", "AdminController@index")->name("index");
            $show = Route::get("/show", "AdminController@show")->name("show");

            $this->assertTrue($index->getName() === "admin.index");
            $this->assertTrue($show->getName() === "admin.show");
        });

        $musics = Route::get("/musics", "musics@index")->name("musics.index");

        $this->assertTrue($musics->getName() === "musics.index");
    }

    /**
     * @test
     */
    public function check_if_the_prefix_of_all_routes_within_the_group_change()
    {
        Route::prefix("admin")->group(function () {
            $index = Route::get("/index", "AdminController@index");
            $create = Route::get("/create", "AdminController@show");

            $this->assertTrue($index->getRoute() === "/admin/index(\/)?");
            $this->assertTrue($create->getRoute() === "/admin/create(\/)?");
        });

        $pants = Route::get("/pants", "pants@index")->name("pants.index");

        $this->assertTrue($pants->getRoute() === "/pants(\/)?");
    }

    /**
     * @test
     */
    public function check_if_the_namespace_of_all_routes_within_the_group_change()
    {
        Route::namespace("App\Controllers")->group(function () {
            $index = Route::get("/index", ["Users", "index"]);
            $create = Route::get("/create", "Users@create");

            $this->assertTrue($index->getHandler() === "\App\Controllers\Users");
            $this->assertTrue($create->getHandler() === "\App\Controllers\Users");
        });

        $outOfGroup = Route::get("/books", [\App\Controllers\Books::class, "index"])->name("books.index");

        $this->assertTrue($outOfGroup->getRoute() === "/books(\/)?");
    }

    /**
     * @test
     */
    public function check_names_and_that_all_routes_within_the_group_have_an_instance_of_RouterGroups()
    {
        Route::namespace("App\Http\Controllers")
            ->prefix("admin")
            ->name("admin.")
            ->group(function () {
                $routes = [
                    Route::get("/", ["Admin", "index"])->name("index"),
                    Route::post("/store", ["Admin", "store"])->name("store"),
                    Route::put("/{id}/edit", ["Admin", "edit"])->name("edit"),
                    Route::patch("/{id}/update", ["Admin", "update"])->name("update"),
                    Route::delete("/{id}/delete", ["Admin", "index"])->name("index")
                ];

                foreach ($routes as $route) {
                    $this->assertInstanceOf(RouteGroups::class, $route->getGroup());

                    $this->assertTrue($route->getHandler() === "\App\Http\Controllers\Admin");
                }
            });
    }

    /**
     * @test
     */
    public function check_if_all_routes_have_group_middleware()
    {
        Route::middleware("isLogged")
            ->namespace("App\Http\Controllers")
            ->prefix("user")
            ->name("user.")
            ->group(function() {
                $routes = [
                    Route::get("/", ["User", "index"])->name("index"),
                    Route::put("/settings", ["User", "updateSettings"])->name("updateSettings"),
                    Route::get("/settings", ["User", "settings"])->name("settings"),
                    Route::get("/logout", ["User", "logout"])->name("logout")
                ];

                $routeWithoutMiddleware = Route::get("/{id}/profile", ["User", "profile"])  
                    ->name("profile")
                    ->withoutMiddleware("isLogged");

                foreach ($routes as $route) {
                    $routeMiddleware = $route->getMiddleware();

                    $this->assertEquals("isLogged", $routeMiddleware->get());
                }

                $this->assertEquals("", $routeWithoutMiddleware->getMiddleware()->get());
            }); 
    }
}
