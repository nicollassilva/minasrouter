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
    public function check_if_the_initiator_created_a_RouteCollection_instance()
    {
        Route::start("http://localhost/");

        $this->assertInstanceOf(
            RouteCollection::class,
            Route::$collection,
            "The start method did not create an instance of RouteCollection! [start]"
        );
    }

    /**
     * @test
     */
    public function check_if_the_routes_restful_methods_returns_an_instance_of_RouteManager()
    {
        $routes = [
            "get" => Route::get("/users", [User::class, "index"]),
            "post" => Route::post("/user", "User@store"),
            "put" => Route::put("/user/{id}/update", [User::class, "update"]),
            "patch" => Route::patch("/user/{id}/update", [User::class, "update"]),
            "delete" => Route::delete("/user/{id}", "User@delete"),
        ];

        foreach ($routes as $method => $route) {
            $this->assertInstanceOf(
                RouteManager::class,
                $route,
                "The route did not return an instance of RouteManager: [\$route[\"$method\"]]"
            );
        }
    }

    /**
     * @test
     */
    public function check_if_name_function_change_route_name()
    {
        $route = Route::get(
            "/dashboard",
            [Dashboard::class, "index"]
        )->name("dashboard.index");

        $this->assertTrue(
            $route->getName() === "dashboard.index",
            "Error in [name] method. One step"
        );

        $routeTwo = Route::post(
            "/dashboard/admins",
            function () {
                echo "Admins list: ...";
            }
        )->name("dashboard.admins");

        $this->assertTrue(
            $routeTwo->getName() === "dashboard.admins",
            "Error in [name] method. Two step"
        );
    }

    /**
     * @test
     */
    public function check_if_the_function_where_the_route_regex_change()
    {
        $routeNumber = Route::get("/profile/{id}", [User::class, "profile"])
            ->where(["id" => "[0-9]+"]);

        $this->assertTrue(
            $routeNumber->getRoute() === "/profile/([0-9]+)(\/)?",
            "Error in [where] method"
        );

        $routeCharacters = Route::put("/profile/{slug}/avatar", [User::class, "changeAvatar"])
            ->whereParam("slug", "[a-z0-9A-Z\-\_]+");

        $this->assertTrue(
            $routeCharacters->getRoute() === "/profile/([a-z0-9A-Z\-\_]+)/avatar(\/)?",
            "Error in [whereParam] method"
        );

        $routeReplacing = Route::delete("/book/{id}/delete", [Book::class, "destroy"])
            ->where(["id" => "[0-9]+"])
            ->whereParam("id", "[0-9a-z]+");

        $this->assertTrue(
            $routeReplacing->getRoute() === "/book/([0-9a-z]+)/delete(\/)?",
            "Error in replacing [where] method to [whereParam] method."
        );

        /** 
         * 
         * Helper methods below
         * 
         */

        $routeNumberMethod = Route::delete(
            "/profile/{id}/delete",
            [User::class, "destroy"]
        )
            ->whereNumber("id")
            ->name("method.number");

        $this->assertTrue(
            $routeNumberMethod->getRoute() === "/profile/([0-9]+)/delete(\/)?",
            "Error in [whereNumber] method"
        );

        $alphaRegex = "[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ]+";

        $routeAlphaMethod = Route::get("/profile/{slug}", [User::class, "profile"])
            ->whereAlpha("slug")
            ->name("method.alpha");

        $this->assertTrue(
            $routeAlphaMethod->getRoute() === "/profile/({$alphaRegex})(\/)?",
            "Error in [whereAlpha] method"
        );

        $routeAlphaNumericMethod = Route::patch("/books/{slug}", [Books::class, "show"])
            ->whereAlphaNumeric("slug")
            ->name("method.alphaNumeric");

        $alphaRegex = rtrim($alphaRegex, "]+") . "0-9]+";

        $this->assertTrue(
            $routeAlphaNumericMethod->getRoute() === "/books/({$alphaRegex})(\/)?",
            "Error in [whereAlphaNumeric] method"
        );

        $routeUuidMethod = Route::put("/notebook/{slug}", [Notebook::class, "show"])
            ->whereUuid("slug")
            ->name("method.uuid");

        $this->assertTrue(
            $routeUuidMethod->getRoute() === "/notebook/((?i)[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12})(\/)?",
            "Error in [whereUuid] method"
        );
    }

    /**
     * @test
     */
    public function check_regular_expressions_of_helpers_methods_where()
    {
        $collection = Route::$collection;

        $methods = [
            "number" => $collection->getByName("method.number", "delete"),
            "alpha" => $collection->getByName("method.alpha", "get"),
            "alphaNumeric" => $collection->getByName("method.alphaNumeric", "patch"),
            "uuid" => $collection->getByName("method.uuid", "put")
        ];

        $expectedMatchRegex = [
            "number" => "/profile/23/delete",
            "alpha" => "/profile/SlÜgáàüÁÀÇçíìïóòúùÚÙÒÓ",
            "alphaNumeric" => "/books/123SlÜg2131á9àüÁÀÇçí23568012ìïó685òúù312ÙÒÓ79",
            "uuid" => [
                "v1" => "/notebook/e22e1622-5c14-11ea-b2f3-0242ac130003",
                "v2" => "/notebook/000001f5-5e9a-21ea-9e00-0242ac130003",
                "v3" => "/notebook/3f703955-aaba-3e70-a3cb-baff6aa3b28f",
                "v4" => "/notebook/1ee9aa1b-6510-4105-92b9-7171bb2f3089",
                "v5" => "/notebook/a8f6ae40-d8a7-58f0-be05-a22f94eca9ec"
            ]
        ];

        foreach ($methods as $methodName => $route) {
            $expectedResult = $expectedMatchRegex[$methodName];
            $routeRegex = $route->getRoute();

            if (!is_array($expectedResult)) {
                $this->assertRegExp("~^{$routeRegex}$~", $expectedResult);
                continue;
            }

            foreach ($expectedResult as $version => $result) {
                $this->assertRegExp("~^{$routeRegex}$~", $result);
            }
        }
    }

    /**
     * @test
     */
    public function check_if_any_and_match_functions_add_routes()
    {
        $httpMethods = ["GET", "POST", "PUT", "PATCH", "DELETE"];
        $resultBeforeAnyMethod = [4, 2, 3, 2, 3];

        Route::any("/allHttpMethods", [AnyController::class, "anyMethod"]);

        foreach ($httpMethods as $index => $httpMethod) {
            $this->assertCount(
                $resultBeforeAnyMethod[$index] + 1,
                Route::$collection->getRoutesOf($httpMethod),
                "The method [{$httpMethod}] has less route than expected. ANY Method"
            );
        }

        Route::match(
            ["GET", "POST"],
            "/multipleMethod",
            [MatchController::class, "matchMethod"]
        );

        foreach ($httpMethods as $index => $httpMethod) {
            if ($httpMethods == "GET" || $httpMethod == "POST") {
                $this->assertCount(
                    $resultBeforeAnyMethod[$index] + 2,
                    Route::$collection->getRoutesOf($httpMethod),
                    "The method [{$httpMethod}] has less route than expected. MATCH Method"
                );
            }
        }
    }

    /**
     * @test
     */
    public function check_if_all_routes_have_an_instance_of_Request()
    {
        $httpMethods = ["GET", "POST", "PUT", "PATCH", "DELETE"];

        foreach ($httpMethods as $method) {
            $routes = Route::$collection->getRoutesOf($method);

            foreach ($routes as $route) {
                $this->assertInstanceOf(
                    Request::class,
                    $route->request(),
                    "The route did not have an instance of Request: [{$route->getRoute()}]"
                );
            }
        }
    }
}
