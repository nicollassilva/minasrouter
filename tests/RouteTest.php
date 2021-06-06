<?php

use MinasRouter\Http\Request;
use MinasRouter\Router\Route;
use PHPUnit\Framework\TestCase;
use MinasRouter\Router\RouteManager;
use MinasRouter\Router\RouteCollection;

final class RouteTest extends TestCase
{
    /**
     * @test
     */
    public function check_if_the_initiator_created_a_collection_instance()
    {
        Route::start("http://localhost/");

        $this->assertInstanceOf(
            RouteCollection::class,
            Route::$collection
        );
    }

    /**
     * @test
     */
    public function check_if_the_routes_restful_methods_returns_an_instance_of_manager()
    {
        $routes = [
            "get" => Route::get("/users", [User::class, 'index']),
            "post" => Route::post("/user", "User@store"),
            "put" => Route::put("/user/{id}/update", [User::class, 'update']),
            "patch" => Route::patch("/user/{id}/update", [User::class, 'update']),
            "delete" => Route::delete("/user/{id}", "User@delete"),
        ];

        foreach ($routes as $route) {
            $this->assertInstanceOf(
                    RouteManager::class,
                    $route
                );
        }
    }

    /**
     * @test
     */
    public function check_if_name_function_change_route_name()
    {
        $route = Route::get(
                '/dashboard', [Dashboard::class, 'index']
            )->name('dashboard.index');

        $this->assertTrue(
                $route->getName() === 'dashboard.index'
            );

        $routeTwo = Route::post(
            '/dashboard/admins', function() { echo 'Admins list: ...'; }
        )->name('dashboard.admins');

        $this->assertTrue(
                $routeTwo->getName() === 'dashboard.admins'
            );
    }

    /**
     * @test
     */
    public function check_if_the_function_where_the_route_regex_change()
    {
        $route = Route::get(
            '/profile/{id}', [User::class, 'profile']
            )->where(['id' => '[0-9]+']);

        $routeTwo = Route::put(
            '/profile/{slug}/avatar', [User::class, 'changeAvatar']
        )->whereParam('slug', '[a-z0-9A-Z\-\_]+');

        $routeThree = Route::delete(
            '/profile/{id}', [User::class, 'deleteProfile']
        )->where(['id' => '[0-9]+'])
         ->whereParam('id', '[0-9a-z]+');

        $this->assertTrue(
                $route->getRoute() === '/profile/([0-9]+)'
            );

        $this->assertTrue(
                $routeTwo->getRoute() === '/profile/([a-z0-9A-Z\-\_]+)/avatar'
            );

        $this->assertTrue(
                $routeThree->getRoute() === '/profile/([0-9a-z]+)'
            );
    }

    /**
     * @test
     */
    public function check_if_any_and_match_functions_add_routes()
    {
        $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $resultBeforeAnyMethod = [3, 2, 2, 1, 2];

        Route::any('/allHttpMethods', [AnyController::class, 'anyMethod']);

        foreach($httpMethods as $index => $httpMethod) {
            $this->assertCount(
                    $resultBeforeAnyMethod[$index] + 1,
                    Route::$collection->getRouteOf($httpMethod)
                );
        }

        Route::match(
            ['GET', 'POST'],
            '/multipleMethod',
            [MatchController::class, 'matchMethod']);

        foreach($httpMethods as $index => $httpMethod) {
            if($httpMethods == 'GET' || $httpMethod == 'POST') {
                $this->assertCount(
                        $resultBeforeAnyMethod[$index] + 2,
                        Route::$collection->getRouteOf($httpMethod)
                    );
            }
        }
    }

    /**
     * @test
    */
    public function check_if_all_routes_have_an_instance_of_request()
    {
        $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        foreach($httpMethods as $method) {
            $routes = Route::$collection->getRouteOf($method);

            foreach($routes as $route) {
                $this->assertInstanceOf(
                        Request::class,
                        $route->request()
                    );
            }
        }
    }
}