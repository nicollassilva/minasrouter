<?php

use MinasRouter\Router\Route;
use PHPUnit\Framework\TestCase;
use MinasRouter\Router\Middlewares\MiddlewareCollection;

final class RouteMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function check_if_the_middleware_method_inserts_a_MiddlewareCollection_instance()
    {
        Route::start("http://localhost/");

        $route = Route::get("/", [\App\Controllers\WebController::class, "index"])
            ->name("index.middlewares")
            ->middleware([\App\Middlewares\isAdmin::class]);

        $this->assertInstanceOf(
            MiddlewareCollection::class,
            $route->getMiddleware(),
            "Route [{$route->getName()}] not have a MiddlewareCollection instance."
        );
    }

    /**
     * @test
     */
    public function check_whether_the_middleware_method_on_a_group_inserts_a_MiddlewareCollection_instance()
    {
        Route::middleware(\App\Middlewares\isAdmin::class)->group(function () {
            $route = Route::put("/user/{id}/update", [\App\Controllers\UserController::class, "update"]);

            $this->assertInstanceOf(
                MiddlewareCollection::class,
                $route->getMiddleware(),
                ""
            );
        });
    }

    /**
     * @test
     */
    public function check_that_a_route_with_a_middleware_method_within_a_route_group_runs_all_middlewares()
    {
        Route::middleware([\App\Middlewares\isLogged::class])->group(function () {
            $routeWithIndividualMiddleware = Route::put("/panel", [\App\Controllers\Panel\PanelController::class, "index"])
                ->middleware(\App\Middlewares\isModerator::class);

            $this->assertSame(
                $routeWithIndividualMiddleware->getMiddleware()->get(),
                ["App\Middlewares\isLogged", "App\Middlewares\isModerator"],
                "Error in route [{$routeWithIndividualMiddleware->getName()}] middlewares."
            );

            $routeWithoutIndividualMiddleware = Route::get("/my-profile", [\App\Controllers\Panel\PanelController::class, "userProfile"]);

            $this->assertSame(
                $routeWithoutIndividualMiddleware->getMiddleware()->get(),
                ["App\Middlewares\isLogged"],
                "Error in route [{$routeWithoutIndividualMiddleware->getName()}] middlewares."
            );

            $routeWithIndividualMiddleware = Route::put("/panel", [\App\Controllers\Panel\PanelController::class, "index"])
                ->middleware([\App\Middlewares\isModerator::class, \App\Middlewares\isAdmin::class]);

            $this->assertSame(
                $routeWithIndividualMiddleware->getMiddleware()->get(),
                ["App\Middlewares\isLogged", "App\Middlewares\isModerator", "App\Middlewares\isAdmin"],
                "Error in route [{$routeWithIndividualMiddleware->getName()}] middlewares."
            );
        });
    }

    /**
     * @test
     */
    public function check_how_middlewares_behave_in_a_real_scenario()
    {
        Route::globalMiddlewares([
            "isAdmin" => \App\Middlewares\IsAdmin::class,
            "isLogged" => \App\Middlewares\IsLogged::class,
            "isModerator" => \App\Middlewares\IsModerator::class,
            "verifyTrustCsrf" => \App\Middlewares\TrustCsrf::class,
            "setLocaleBySession" => \App\Middlewares\SetLocaleBySession::class
        ]);

        Route::namespace("App\Controllers\Dashboard")
            ->prefix("dashboard/")
            ->name("dashboard.")
            ->middlewares(["verifyTrustCsrf", "setLocaleBySession"])
            ->group(function () {
                $routes = [
                    Route::get("/", "DashboardController@index")->name("index"),
                    Route::get("/login", "DashboardController@login")->name("login"),
                    Route::get("/forgout-password", "DashboardController@forgoutPassword")->name("forgout.password"),
                    Route::get("/app", "AppController@index")->name("app.index")->middleware("isLogged"),
                    Route::get("/admins", "AppController@admins")->name("app.admins")->middleware("isAdmin, isLogged"),
                    Route::get("/forgot-email", "DashboardController@forgoutEmail")->name("forgot.email"),
                    Route::get("/server-status", "DashboardController@serverStatus")->name("server.status"),
                    Route::get("/posts", "AppController@posts")->name("app.posts")->middleware(["isLogged", "isModerator"]),
                    Route::get("/admin-ranking", "DashboardController@adminRanking")->name("admin.ranking")
                ];

                $routesWithIndividualMiddlewares = [
                    "dashboard.app.index" => ["verifyTrustCsrf", "setLocaleBySession", "isLogged"],
                    "dashboard.app.posts" => ["verifyTrustCsrf", "setLocaleBySession", "isLogged", "isModerator"],
                    "dashboard.app.admins" => ["verifyTrustCsrf", "setLocaleBySession", "isAdmin", "isLogged"]
                ];

                foreach ($routes as $route) {
                    $routeName = $route->getName();
                    $routeCompleteMiddleware = $route->getMiddleware()->get();

                    if (array_key_exists($routeName, $routesWithIndividualMiddlewares)) {
                        $this->assertSame(
                            $routesWithIndividualMiddlewares[$routeName],
                            $routeCompleteMiddleware,
                            "Error in route [{$routeName}] middlewares."
                        );

                        continue;
                    }

                    $this->assertSame(
                        $routeCompleteMiddleware,
                        ["verifyTrustCsrf", "setLocaleBySession"],
                        "Error in route [{$routeName}] middlewares."
                    );
                }
            });

        $indexRoute = Route::get("/", [\App\Controllers\WebController::class, "index"])->middleware("verifyTrustCsrf");

        $this->assertSame(
            $indexRoute->getMiddleware()->get(),
            "verifyTrustCsrf"
        );
    }
}
